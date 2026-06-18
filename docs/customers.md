# Customers & Vehicles

The CRM side of a shop: who is being billed, and which vehicle the work is for. Every POS order references a **customer** and (usually) a **vehicle** — see [orders.md](orders.md).

These two modules follow the same controller → FormRequest → repository → model → view pattern and AJAX listing conventions described in [catalog.md](catalog.md#catalog). For schema, see [database.md](database.md); for permissions, [rbac.md](rbac.md).

---

## Customers

Captures registered customers, walk-ins, and corporate accounts.

- **Routes** — [routes/tenant.php:146-165](../routes/tenant.php#L146-L165): `index`, `listing`, `edit`, `save`, `destroy`, gated on `customer.{view,create,update,delete}` plus `customers.{view,manage}`.
- **Controller**: [`Tenant\CustomerController`](../app/Http/Controllers/Tenant/CustomerController.php)
- **Validation**: [`SaveCustomerRequest`](../app/Http/Requests/Tenant/Crm/SaveCustomerRequest.php)
- **Repository**: [`CustomersRepository`](../app/Repositories/CustomersRepository.php)
- **Model**: [`Customer`](../app/Models/Customer.php) — fields:
  - **Identification**: `customer_type` ∈ `registered | walk_in | corporate`, `name`, `phone`, `email`, `address`, `notes`, `date_of_birth`.
  - **Engagement**: `total_visits`, `lifetime_value`, `loyalty_points_balance`, `credit_balance`, `last_visit_at`.
  - **Discount tier**: `discount_group_id` → a [discount group](catalog.md#discount-groups), applied automatically at checkout.
  - **Walk-in shortcut**: `Customer::DEFAULT_WALK_IN_NAME` (`"Walk-in Customer"`) via `Customer::defaultWalkInName()`.
- **Search scope**: name, phone, email, address, plus joined vehicle plate/make/model/registration.
- **Views**: [resources/views/tenant/ecommerce/customers/](../resources/views/tenant/ecommerce/customers/)

> **POS guard**: when saving an order, [`SaveOrderRequest`](../app/Http/Requests/Employee/Orders/SaveOrderRequest.php) requires the chosen customer to have *real* details (a non-placeholder name, or a phone/email/address) — a bare walk-in record with no details is rejected at checkout. See [orders.md](orders.md).

## Vehicles

Customer-owned vehicles, used for service history and reminder generation.

- **Routes** — [routes/tenant.php:167-186](../routes/tenant.php#L167-L186): `index`, `listing`, `edit`, `save`, `destroy`, gated on `vehicle.{view,create,update,delete}` plus `vehicles.{view,manage}`.
- **Controller**: [`Tenant\VehicleController`](../app/Http/Controllers/Tenant/VehicleController.php)
- **Validation**: [`SaveVehicleRequest`](../app/Http/Requests/Tenant/Crm/SaveVehicleRequest.php)
- **Repository**: [`VehiclesRepository`](../app/Repositories/VehiclesRepository.php)
- **Model**: [`Vehicle`](../app/Models/Vehicle.php) — fields: `customer_id`, `plate_number`, `registration_number`, `make`, `model`, `year`, `color`, `engine_type`, `odometer`, `notes`, `is_default`. `belongsTo Customer`. A customer's first vehicle may be marked `is_default = true`, available via `$customer->defaultVehicle`.
- **Views**: [resources/views/tenant/ecommerce/vehicles/](../resources/views/tenant/ecommerce/vehicles/)

> **POS guard**: a `vehicle_id` on an order must belong to the order's customer, and (unless the shop has `orders.vehicle_required = false`) is mandatory. See [settings.md](settings.md) for the toggle and [orders.md](orders.md) for the validation.

---

## Quick reference

| Module | Route prefix | Controller | Repository | Permission group |
|--------|--------------|------------|------------|-------------------|
| Customers | `/tenant/ecommerce/customers` | `Tenant\CustomerController` | `CustomersRepository` | `customer.*` / `customers.*` |
| Vehicles | `/tenant/ecommerce/vehicles` | `Tenant\VehicleController` | `VehiclesRepository` | `vehicle.*` / `vehicles.*` |

**Next in the journey:** [orders.md](orders.md) — selling to these customers through the POS.
</content>
