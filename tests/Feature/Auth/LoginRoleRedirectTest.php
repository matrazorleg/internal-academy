<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRoleRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_admin_is_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.workshops.index'));
    }

    public function test_employee_is_redirected_to_employee_dashboard_after_login(): void
    {
        $employee = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $employee->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));
    }
}
