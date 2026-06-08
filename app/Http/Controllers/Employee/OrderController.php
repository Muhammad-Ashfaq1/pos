<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\Orders\SaveOrderRequest;
use App\Mail\ShareOrderMail;
use App\Models\Order;
use App\Repositories\Interface\OrderRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function index(): View
    {
        return view('employee.order.index');
    }

    public function create(): View
    {
        return view('employee.order.new-order');
    }

    public function listing(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'tab' => ['nullable', 'string', 'in:today,all,pending,estimates'],
            'q' => ['nullable', 'string', 'max:100'],
            'sort' => [
                'nullable',
                'string',
                Rule::in([
                    'latest',
                    'oldest',
                    'amount_desc',
                    'amount_asc',
                    'customer_name',
                    'date_opened',
                    'order_id',
                    'order_total',
                ]),
            ],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'search_fields' => ['nullable', 'array'],
            'search_fields.*' => [
                'string',
                Rule::in(['order_number', 'customer_id', 'paid_status', 'retailer', 'time', 'date']),
            ],
        ]);

        return response()->json($this->orderRepository->listing($filters));
    }

    public function show(Order $order): View
    {
        return view('employee.order.show', [
            'order' => $this->orderRepository->details($order),
        ]);
    }

    public function store(SaveOrderRequest $request): JsonResponse
    {
        $result = $this->orderRepository->store($request->validated(), $request->user());

        return response()->json([
            'message' => $result['message'],
            'data' => $result['data'],
        ]);
    }

    public function pay(Order $order, Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,card,check'],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
        ], [
            'payment_amount.min' => 'Payment amount must be greater than zero.',
        ]);

        $result = $this->orderRepository->addPayment($order, $data, $request->user());

        return response()->json($result);
    }

    public function print(Order $order): View
    {
        $details = $this->orderRepository->details($order);

        return view('employee.order.print', [
            'order' => $order,
            'details' => $details,
        ]);
    }

    public function pdf(Order $order): Response
    {
        $details = $this->orderRepository->details($order);
        $pdf = Pdf::loadView('employee.order.pdf', [
            'order' => $order,
            'details' => $details,
        ]);

        $filename = $order->status === Order::STATUS_ESTIMATE
            ? "estimate-{$order->order_number}.pdf"
            : "invoice-{$order->order_number}.pdf";

        return $pdf->download($filename);
    }

    public function share(Order $order, Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = $data['email'];
        $customer = $order->customer;

        if ($customer) {
            if ($customer->email !== $email) {
                $customer->update(['email' => $email]);
            }
        }

        $details = $this->orderRepository->details($order);
        $pdf = Pdf::loadView('employee.order.pdf', [
            'order' => $order,
            'details' => $details,
        ]);

        $filename = $order->status === Order::STATUS_ESTIMATE
            ? "estimate-{$order->order_number}.pdf"
            : "invoice-{$order->order_number}.pdf";

        Mail::to($email)->send(new ShareOrderMail($order, $pdf->output(), $filename));

        return response()->json([
            'success' => true,
            'message' => "Document shared successfully with {$email}.",
        ]);
    }
}
