<?php

namespace NCh\Codeforlife\Tests;

class RouteTest extends TestCase
{
    protected $withDummy = true;

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
    public function testGetRoutes()
    {
        $this->visit(route('codeforlife.login'));
        $this->type('admin@admin.com', 'email');
        $this->type('password', 'password');
        $this->press('Login');

        $urls = [
            route('codeforlife.dashboard'),
            route('codeforlife.media.index'),
            route('codeforlife.settings.index'),
            route('codeforlife.roles.index'),
            route('codeforlife.roles.create'),
            route('codeforlife.roles.show', ['role' => 1]),
            route('codeforlife.roles.edit', ['role' => 1]),
            route('codeforlife.users.index'),
            route('codeforlife.users.create'),
            route('codeforlife.users.show', ['user' => 1]),
            route('codeforlife.users.edit', ['user' => 1]),
            route('codeforlife.posts.index'),
            route('codeforlife.posts.create'),
            route('codeforlife.posts.show', ['post' => 1]),
            route('codeforlife.posts.edit', ['post' => 1]),
            route('codeforlife.pages.index'),
            route('codeforlife.pages.create'),
            route('codeforlife.pages.show', ['page' => 1]),
            route('codeforlife.pages.edit', ['page' => 1]),
            route('codeforlife.categories.index'),
            route('codeforlife.categories.create'),
            route('codeforlife.categories.show', ['category' => 1]),
            route('codeforlife.categories.edit', ['category' => 1]),
            route('codeforlife.menus.index'),
            route('codeforlife.menus.create'),
            route('codeforlife.menus.show', ['menu' => 1]),
            route('codeforlife.menus.edit', ['menu' => 1]),
            route('codeforlife.database.index'),
            //route('codeforlife.database.edit_bread', ['id' => 5]),
            //route('codeforlife.database.edit', ['table' => 'categories']),
            route('codeforlife.database.create'),
        ];

        foreach ($urls as $url) {
            $response = $this->call('GET', $url);
            $this->assertEquals(200, $response->status(), $url.' did not return a 200');
        }
    }
}
