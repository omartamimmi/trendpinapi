# Admin Module

The Admin module provides a comprehensive administrative interface for managing the TrendPin platform. It follows best practices including Repository Pattern, Service Layer, and proper OOP design.

## Architecture

```
Modules/Admin/
├── app/
│   ├── Events/                    # Domain events
│   │   ├── AdminLoggedIn.php
│   │   ├── AdminLoggedOut.php
│   │   ├── UserCreated.php
│   │   ├── OnboardingApproved.php
│   │   └── OnboardingRejected.php
│   ├── Exceptions/                # Custom exceptions
│   │   ├── InvalidCredentialsException.php
│   │   └── UnauthorizedAccessException.php
│   ├── Http/
│   │   ├── Controllers/           # HTTP controllers
│   │   │   ├── AuthController.php
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── AdminUserController.php
│   │   │   ├── AdminPlanController.php
│   │   │   ├── AdminOnboardingController.php
│   │   │   └── ...
│   │   ├── Middleware/            # Admin-specific middleware
│   │   │   ├── AdminAuthenticate.php
│   │   │   ├── EnsureAdminRole.php
│   │   │   └── AdminActivityLog.php
│   │   ├── Requests/              # Form request validation
│   │   │   ├── LoginRequest.php
│   │   │   ├── CreateUserRequest.php
│   │   │   ├── UpdateUserRequest.php
│   │   │   ├── CreatePlanRequest.php
│   │   │   └── ...
│   │   └── Resources/             # API resources
│   │       ├── UserResource.php
│   │       ├── UserCollection.php
│   │       ├── PlanResource.php
│   │       ├── OnboardingResource.php
│   │       └── DashboardStatsResource.php
│   ├── Listeners/                 # Event listeners
│   │   ├── LogAdminActivity.php
│   │   ├── SendOnboardingApprovedNotification.php
│   │   └── SendOnboardingRejectedNotification.php
│   ├── Policies/                  # Authorization policies
│   │   ├── UserPolicy.php
│   │   ├── PlanPolicy.php
│   │   └── OnboardingPolicy.php
│   ├── Providers/                 # Service providers
│   │   ├── AdminServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── RouteServiceProvider.php
│   ├── Repositories/              # Data access layer
│   │   ├── Contracts/
│   │   │   ├── BaseRepositoryInterface.php
│   │   │   ├── UserRepositoryInterface.php
│   │   │   ├── PlanRepositoryInterface.php
│   │   │   └── OnboardingRepositoryInterface.php
│   │   ├── BaseRepository.php
│   │   ├── UserRepository.php
│   │   ├── PlanRepository.php
│   │   └── OnboardingRepository.php
│   └── Services/                  # Business logic layer
│       ├── Contracts/
│       │   ├── AuthServiceInterface.php
│       │   ├── UserServiceInterface.php
│       │   ├── PlanServiceInterface.php
│       │   ├── OnboardingServiceInterface.php
│       │   └── DashboardServiceInterface.php
│       ├── AuthService.php
│       ├── UserService.php
│       ├── PlanService.php
│       ├── OnboardingService.php
│       └── DashboardService.php
├── config/
│   └── admin.php                  # Module configuration
├── routes/
│   ├── web.php                    # Web routes
│   └── api.php                    # API routes
└── tests/
    ├── Feature/                   # Feature tests
    │   ├── AdminAuthTest.php
    │   ├── AdminUsersTest.php
    │   └── AdminPlansTest.php
    └── Unit/                      # Unit tests
        └── Services/
            ├── AuthServiceTest.php
            ├── UserServiceTest.php
            ├── PlanServiceTest.php
            └── DashboardServiceTest.php
```

## Design Patterns

### Repository Pattern
All data access is abstracted through repository interfaces:

```php
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function getByRole(string $role): Collection;
    public function paginateWithRoles(?string $search = null, int $perPage = 20): LengthAwarePaginator;
}
```

### Service Layer
Business logic is encapsulated in service classes:

```php
class UserService implements UserServiceInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function createUser(array $data): User
    {
        $user = $this->userRepository->create([...]);
        $user->assignRole($data['role']);
        event(new UserCreated($user));
        return $user;
    }
}
```

### Dependency Injection
All dependencies are bound through the service provider:

```php
// AdminServiceProvider.php
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);
$this->app->bind(UserServiceInterface::class, UserService::class);
```

## Features

### User Management
- List all users with pagination and search
- Create new users with role assignment
- Update user information and roles
- Delete users (with restrictions for admins)

### Plan Management
- Manage subscription plans (user, retailer, bank)
- Create, update, delete plans
- Filter plans by type
- Toggle plan active status

### Onboarding Management
- Review retailer onboarding applications
- Approve, reject, or request changes
- View onboarding details and documents
- Track approval status counts

### Dashboard
- User statistics (total, this month)
- Onboarding statistics (pending, approved, rejected)
- Plan statistics (total, active)
- Revenue metrics (cached for performance)

## API Endpoints

### Authentication
```
POST /api/v1/admin/login     - Admin login
POST /api/v1/admin/logout    - Admin logout
GET  /api/v1/admin/me        - Get current admin user
```

### Users
```
GET    /api/v1/admin/users       - List users
POST   /api/v1/admin/users       - Create user
GET    /api/v1/admin/users/{id}  - Get user
PUT    /api/v1/admin/users/{id}  - Update user
DELETE /api/v1/admin/users/{id}  - Delete user
```

## Web Routes

All routes are prefixed with `/admin` and require `auth` and `role:admin` middleware.

### Dashboard
- `GET /admin/dashboard` - Main dashboard with statistics

### Users
- `GET /admin/users` - List all users
- `POST /admin/users` - Create new user
- `PUT /admin/users/{id}` - Update user
- `DELETE /admin/users/{id}` - Delete user

### Plans
- `GET /admin/plans` - List subscription plans
- `POST /admin/plans` - Create plan
- `PUT /admin/plans/{id}` - Update plan
- `DELETE /admin/plans/{id}` - Delete plan

### Onboarding Approvals
- `GET /admin/onboarding-approvals` - List pending approvals
- `GET /admin/onboarding-approvals/{id}` - Review onboarding
- `POST /admin/onboarding-approvals/{id}/approve` - Approve
- `POST /admin/onboarding-approvals/{id}/request-changes` - Request changes
- `POST /admin/onboarding-approvals/{id}/reject` - Reject

## Events

| Event | Description |
|-------|-------------|
| `AdminLoggedIn` | Fired when an admin logs in |
| `AdminLoggedOut` | Fired when an admin logs out |
| `UserCreated` | Fired when a user is created by admin |
| `OnboardingApproved` | Fired when an onboarding is approved |
| `OnboardingRejected` | Fired when an onboarding is rejected |

## Middleware

| Middleware | Description |
|------------|-------------|
| `AdminAuthenticate` | Ensures user is authenticated as admin |
| `EnsureAdminRole` | Checks user has required admin role |
| `AdminActivityLog` | Logs admin actions for audit trail |

## Configuration

Configuration is stored in `config/admin.php`:

```php
return [
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100,
    ],
    'plan_types' => ['user', 'retailer', 'bank'],
    'onboarding_statuses' => [
        'pending', 'pending_approval', 'approved',
        'changes_requested', 'rejected',
    ],
    'cache' => [
        'stats_ttl' => 300, // 5 minutes
    ],
];
```

## Dependencies

- `Modules\RetailerOnboarding` - For subscription and onboarding models
- `Modules\Business` - For brand and branch models
- `Modules\Category` - For category model
- `Modules\Notification` - For notification models
- `Spatie\Permission` - For role and permission management

## Testing

Run tests with:

```bash
# All admin tests
php artisan test --filter=Admin

# Unit tests only
php artisan test Modules/Admin/tests/Unit

# Feature tests only
php artisan test Modules/Admin/tests/Feature

# Specific test
php artisan test --filter=AuthServiceTest
```

## Security

- All admin routes require authentication
- Role-based access control (super_admin, admin, moderator)
- Sensitive data is excluded from activity logs
- Admin actions are logged for audit trails
- Super admin cannot be deleted
- Users cannot delete themselves

## Best Practices Implemented

1. **Single Responsibility Principle** - Each class has one responsibility
2. **Open/Closed Principle** - Interfaces allow extension without modification
3. **Liskov Substitution** - Implementations can replace interfaces
4. **Interface Segregation** - Focused interfaces for specific needs
5. **Dependency Inversion** - Depend on abstractions, not concretions

## Frontend

Uses Inertia.js with React components located in `resources/js/Pages/Admin/`.
