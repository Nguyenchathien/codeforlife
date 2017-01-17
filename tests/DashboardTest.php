<?php

namespace NCH\Codeforlife\Tests;

use Illuminate\Support\Facades\Auth;

class DashboardTest extends TestCase
{
    protected $withDummy = true;

    public function setUp()
    {
        parent::setUp();

        $this->install();
    }

    public function testWeHaveAccessToTheMainSections()
    {
        // We must first login and visit the dashboard page.
        Auth::loginUsingId(1);
        $this->visit(route('codeforlife.dashboard'));

        $this->see('Dashboard');

        // We can see number of Users.
        $this->see('1 Users');

        // list them.
        $this->click('View All Users');
        $this->seePageIs(route('codeforlife.users.index'));

        // and return to dashboard from there.
        $this->click('Dashboard');
        $this->seePageIs(route('codeforlife.dashboard'));

        // We can see number of posts.
        $this->see('4 Post(s)');

        // list them.
        $this->click('View All Posts');
        $this->seePageIs(route('codeforlife.posts.index'));

        // and return to dashboard from there.
        $this->click('Dashboard');
        $this->seePageIs(route('codeforlife.dashboard'));

        // We can see number of Pages.
        $this->see('1 Page(s)');

        // list them.
        $this->click('View All Pages');
        $this->seePageIs(route('codeforlife.pages.index'));

        // and return to Dashboard from there.
        $this->click('Dashboard');
        $this->seePageIs(route('codeforlife.dashboard'));
        $this->see('Dashboard');
    }
}
