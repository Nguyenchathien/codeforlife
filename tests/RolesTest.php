<?php

namespace NCH\Codeforlife\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NCH\Codeforlife\Models\Role;

class RolesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        $this->install();
    }

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testRoles()
    {
        $this->visit(route('codeforlife.login'));
        $this->type('admin@admin.com', 'email');
        $this->type('password', 'password');
        $this->press('Login');
        $this->seePageIs(route('codeforlife.dashboard'));

        // Adding a New Role
        $this->visit(route('codeforlife.roles.index'))->click('Add New')->seePageIs(route('codeforlife.roles.create'));
        $this->type('superadmin', 'name');
        $this->type('Super Admin', 'display_name');
        $this->press('Submit');
        $this->seePageIs(route('codeforlife.roles.index'));
        $this->seeInDatabase('roles', ['name' => 'superadmin']);

        // Editing a Role
        $this->visit(route('codeforlife.roles.edit', 2));
        $this->type('regular_user', 'name');
        $this->press('Submit');
        $this->seePageIs(route('codeforlife.roles.index'));
        $this->seeInDatabase('roles', ['name' => 'regular_user']);

        // Editing a Role
        $this->visit(route('codeforlife.roles.edit', 2));
        $this->type('user', 'name');
        $this->press('Submit');
        $this->seePageIs(route('codeforlife.roles.index'));
        $this->seeInDatabase('roles', ['name' => 'user']);

        // Get the current super admin role
        $superadmin_role = Role::where('name', '=', 'superadmin')->first();

        // Deleting a Role
        $response = $this->call('DELETE', route('codeforlife.roles.destroy', $superadmin_role->id), ['_token' => csrf_token()]);
        $this->assertEquals(302, $response->getStatusCode());
        $this->notSeeInDatabase('roles', ['name' => 'superadmin']);
    }
}
