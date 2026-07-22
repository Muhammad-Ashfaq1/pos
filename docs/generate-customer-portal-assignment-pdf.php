<?php

/**
 * One-off generator: Customer Portal developer assignment PDF.
 * Run: php docs/generate-customer-portal-assignment-pdf.php
 */

require __DIR__.'/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$html = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Portal — Developer Assignment</title>
<style>
  @page { margin: 28px 32px; }
  body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 11px;
    color: #1f2937;
    line-height: 1.45;
  }
  h1 {
    margin: 0 0 6px;
    font-size: 20px;
    color: #312e81;
  }
  h2 {
    margin: 18px 0 8px;
    padding-bottom: 4px;
    border-bottom: 2px solid #fbbf24;
    font-size: 13px;
    color: #312e81;
  }
  h3 {
    margin: 12px 0 6px;
    font-size: 11.5px;
    color: #262363;
  }
  .meta {
    margin-bottom: 14px;
    padding: 10px 12px;
    background: #eef2ff;
    border: 1px solid #c7d2fe;
    border-radius: 6px;
  }
  .meta p { margin: 3px 0; }
  .badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 4px;
    background: #312e81;
    color: #fff;
    font-size: 9px;
    font-weight: bold;
  }
  .badge-amber {
    background: #fbbf24;
    color: #1f2937;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 8px 0 12px;
  }
  th, td {
    border: 1px solid #d1d5db;
    padding: 6px 8px;
    text-align: left;
    vertical-align: top;
  }
  th {
    background: #312e81;
    color: #fff;
    font-size: 10px;
  }
  tr:nth-child(even) td { background: #f8fafc; }
  ul { margin: 6px 0 10px 18px; padding: 0; }
  li { margin-bottom: 3px; }
  .small { font-size: 10px; color: #64748b; }
  .box {
    margin: 8px 0 12px;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #fafafa;
  }
  .footer {
    margin-top: 20px;
    padding-top: 8px;
    border-top: 1px solid #e5e7eb;
    font-size: 9px;
    color: #64748b;
  }
  .check { color: #15803d; font-weight: bold; }
  code {
    font-family: DejaVu Sans Mono, monospace;
    font-size: 9.5px;
    background: #f1f5f9;
    padding: 1px 4px;
    border-radius: 3px;
  }
</style>
</head>
<body>

  <h1>Customer Portal — Developer Assignment</h1>
  <p class="small">Laravel / POS project &nbsp;|&nbsp; Assign to: Laravel Developer &nbsp;|&nbsp; Generated: 22 Jul 2026</p>

  <div class="meta">
    <p><span class="badge">EPIC</span> &nbsp; Finish the remaining Customer Portal work (web session portal + employee POS credit redeem).</p>
    <p><strong>Already done (do not rebuild):</strong> store-credit wallet, ledger, earn-on-paid, shared <code>/login</code> for customers, portal pages scaffold, API credits/vehicles, order-detail “Use Store Credit” (any balance &gt; 0).</p>
    <p><strong>Your job:</strong> UI consistency, credits history polish, POS new-order credit with ≥50 unlock rule, vehicles / password / PDF, docs + QA.</p>
  </div>

  <h2>1. Locked product rules</h2>
  <ul>
    <li><strong>Credit unlock:</strong> Customer balance must be <strong>≥ tenant setting</strong> (default <strong>50</strong> in shop currency) before any store credit can be applied. Once unlocked, partial redemption up to balance/due is allowed.</li>
    <li><strong>In scope:</strong> vehicles page, change password, order PDF.</li>
    <li><strong>Out of scope:</strong> Flutter UI, credit expiry worker, appointments, separate customer login page.</li>
  </ul>

  <h2>2. Reference files</h2>
  <div class="box">
    <p><strong>Docs:</strong> <code>docs/customer-portal.md</code></p>
    <p><strong>Portal:</strong> <code>routes/customer.php</code>, <code>app/Http/Controllers/Customer/PortalController.php</code>, <code>resources/views/customer/*</code>, <code>resources/views/layouts/customer-portal.blade.php</code></p>
    <p><strong>Credits:</strong> <code>app/Services/CreditService.php</code>, <code>app/Repositories/OrdersRepository.php</code> (<code>payment.credits_applied</code>)</p>
    <p><strong>POS gap:</strong> <code>public/assets/js/employee/new-order.js</code> (no <code>credits_applied</code> yet), <code>resources/views/employee/order/new-order.blade.php</code></p>
    <p><strong>UI reference:</strong> employee portal patterns — <code>x-employee.page-header</code>, <code>public/assets/css/employee-orders.css</code> (adapt; don’t hard-couple customer routes to employee CSS)</p>
    <p><strong>Settings pattern:</strong> <code>Tenant::DEFAULT_SETTINGS</code> + <code>ShopSettingsRepository</code> (loyalty section)</p>
    <p><strong>Tests:</strong> <code>tests/Feature/Customer/CustomerPortalCreditTest.php</code></p>
  </div>

  <h2>3. Ticket backlog (assignable)</h2>

  <h3>Epic A — Match customer portal UI to the application</h3>
  <table>
    <thead>
      <tr><th style="width:8%">ID</th><th style="width:52%">Task</th><th style="width:28%">Notes</th><th style="width:12%">Est.</th></tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>A1</strong></td>
        <td>Shared customer page chrome + CSS (header, tabs, panels)</td>
        <td>New CSS e.g. <code>customer-portal.css</code>; optional <code>x-customer.page-header</code>; replace nav-pills with underline tabs</td>
        <td>M</td>
      </tr>
      <tr>
        <td><strong>A2</strong></td>
        <td>Restyle all portal pages to match app</td>
        <td>dashboard, orders, order-show, credits, profile, reset — form-label / form-control / btn-primary / btn-label-secondary</td>
        <td>M</td>
      </tr>
    </tbody>
  </table>
  <p class="small"><strong>Acceptance:</strong> Desktop + mobile; empty states; Notiflix toasts unchanged; visual parity with employee Cards/Orders.</p>

  <h3>Epic B — Credits history polish</h3>
  <table>
    <thead>
      <tr><th style="width:8%">ID</th><th style="width:52%">Task</th><th style="width:28%">Notes</th><th style="width:12%">Est.</th></tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>B1</strong></td>
        <td>Polish <code>/portal/credits</code></td>
        <td>Balance hero; filters earn/redeem/adjust; type badges; order link; balance after; empty + pagination</td>
        <td>S</td>
      </tr>
      <tr>
        <td><strong>B2</strong></td>
        <td>Dashboard credit summary</td>
        <td>Balance + last few txns + CTA; show unlock messaging until balance ≥ threshold</td>
        <td>S</td>
      </tr>
    </tbody>
  </table>
  <p class="small"><strong>Note:</strong> History API/web already exist — polish UX, don’t rebuild ledger.</p>

  <h3>Epic C — Use store credit on new order (employee POS)</h3>
  <table>
    <thead>
      <tr><th style="width:8%">ID</th><th style="width:44%">Task</th><th style="width:24%">Depends</th><th style="width:12%">Est.</th><th style="width:12%">Priority</th></tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>C1</strong></td>
        <td>Tenant setting <code>loyalty.credit_min_redeem_balance</code> (default 50) + server gate</td>
        <td>—</td>
        <td>S</td>
        <td><span class="badge badge-amber">P0</span></td>
      </tr>
      <tr>
        <td><strong>C2</strong></td>
        <td>New-order Store Credit UI + include <code>payment.credits_applied</code> in checkout payload</td>
        <td>C1</td>
        <td>L</td>
        <td><span class="badge badge-amber">P0</span></td>
      </tr>
      <tr>
        <td><strong>C3</strong></td>
        <td>Order-detail Pay Balance modal: same ≥ threshold gate (today any balance &gt; 0)</td>
        <td>C1</td>
        <td>S</td>
        <td><span class="badge">P1</span></td>
      </tr>
      <tr>
        <td><strong>C4</strong></td>
        <td>Feature/unit tests: reject below threshold; allow above; over-redeem still blocked</td>
        <td>C1–C3</td>
        <td>S</td>
        <td><span class="badge">P1</span></td>
      </tr>
    </tbody>
  </table>
  <div class="box">
    <strong>C2 detail:</strong> When selected customer balance ≥ threshold → show amount / Max / remaining. When below → disabled + message. Wire into <code>currentOrderPayload().payment.credits_applied</code>. Backend already redeems in <code>OrdersRepository::store</code>. Enforce threshold in one shared place (e.g. <code>CreditService</code> + <code>SaveOrderRequest</code>).
  </div>

  <h3>Epic D — More customer self-service</h3>
  <table>
    <thead>
      <tr><th style="width:8%">ID</th><th style="width:52%">Task</th><th style="width:28%">Notes</th><th style="width:12%">Est.</th></tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>D1</strong></td>
        <td>Web portal Vehicles page + nav tab</td>
        <td>Read-only list OK initially; API <code>GET /api/v1/customer/vehicles</code> already exists</td>
        <td>M</td>
      </tr>
      <tr>
        <td><strong>D2</strong></td>
        <td>Change password while logged in</td>
        <td>Current + new + confirm on profile; Laravel Password rules</td>
        <td>S</td>
      </tr>
      <tr>
        <td><strong>D3</strong></td>
        <td>Customer order / visit PDF download</td>
        <td>Reuse <code>employee.order.pdf</code>; authorize own orders only</td>
        <td>M</td>
      </tr>
    </tbody>
  </table>

  <h3>Epic E — Docs &amp; handoff</h3>
  <table>
    <thead>
      <tr><th style="width:8%">ID</th><th style="width:70%">Task</th><th style="width:12%">Est.</th><th style="width:10%">Done?</th></tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>E1</strong></td>
        <td>Update <code>docs/customer-portal.md</code> (threshold setting, new-order redeem, new pages)</td>
        <td>S</td>
        <td>☐</td>
      </tr>
      <tr>
        <td><strong>E2</strong></td>
        <td>QA checklist pass (see section 4)</td>
        <td>S</td>
        <td>☐</td>
      </tr>
    </tbody>
  </table>

  <h2>4. QA checklist (Definition of Done)</h2>
  <ul>
    <li>☐ Login via shared <code>/login</code> → lands on <code>/portal</code></li>
    <li>☐ Portal UI matches app (tabs, headers, forms, indigo theme)</li>
    <li>☐ Credits history filters + pagination work</li>
    <li>☐ New order: customer balance &lt; 50 → credit locked with clear message</li>
    <li>☐ New order: balance ≥ 50 → apply partial/full credit; payment row shows <code>store_credit</code></li>
    <li>☐ Order-detail pay modal respects same threshold</li>
    <li>☐ Vehicles page lists customer’s vehicles only</li>
    <li>☐ Change password works; wrong current password rejected</li>
    <li>☐ Order PDF downloads for own order only</li>
    <li>☐ Regression: earn-on-paid still works; staff adjust credit still works</li>
    <li>☐ Tests green for credit threshold + existing portal credit tests</li>
  </ul>

  <h2>5. Suggested build order</h2>
  <p>A1 → A2 → B1/B2 → <strong>C1 → C2 → C3 → C4</strong> → D1 → D2 → D3 → E1/E2</p>
  <p class="small">Ship C1+C2 first if POS credit is the business priority; UI polish can run in parallel.</p>

  <h2>6. Sign-off</h2>
  <table>
    <tr><th style="width:30%">Role</th><th style="width:35%">Name</th><th style="width:35%">Date / Signature</th></tr>
    <tr><td>Assigned developer</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>Reviewer / PM</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>Accepted (DoD met)</td><td>&nbsp;</td><td>&nbsp;</td></tr>
  </table>

  <div class="footer">
    POS — Customer Portal Developer Assignment &nbsp;|&nbsp; Est. sizes: S ≈ 0.5–1d, M ≈ 1–2d, L ≈ 2–3d &nbsp;|&nbsp; Do not rebuild CreditService wallet/ledger — extend it.
  </div>

</body>
</html>
HTML;

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$out = __DIR__.'/Customer-Portal-Developer-Assignment.pdf';
file_put_contents($out, $dompdf->output());

echo "Wrote: {$out}\n";
