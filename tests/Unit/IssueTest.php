<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class IssueTest extends TestCase
{
    use DatabaseMigrations;

    protected $issue;

    public function setUp()
    {
        parent::setUp();

        $this->issue = factory('App\Issue')->create();
    }

    /** @test */
    function an_issue_has_a_creator()
    {
        $this->assertInstanceOf('App\User', $this->issue->creator);
    }

    /** @test */
    function an_issue_has_replies()
    {
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->issue->replies);
    }

    /** @test */
    function an_issue_can_add_a_reply()
    {
        $this->issue->addReply([
            'body' => 'FooBar',
            'user_id' => 1
        ]);

        $this->assertCount(1, $this->issue->replies);
    }

    /** @test */
    function an_issue_belongs_to_a_category()
    {
        $issue = create('App\Issue');

        $this->assertInstanceOf('App\Category', $issue->category);
    }

    /** @test */
    function an_issue_can_be_subscribed_to()
    {
        $issue = create('App\Issue');

        $issue->subscribe($userId = 1);

        $this->assertEquals(1, $issue->subscriptions()->where('user_id', $userId)->count());
    }

    /** @test */
    function an_issue_can_be_unsubscribed_from()
    {
        $issue = create('App\Issue');

        $issue->subscribe($userId = 1);

        $issue->unSubscribe($userId);

        $this->assertEquals(0, $issue->subscriptions()->where('user_id', $userId)->count());
    }

    /** @test */
    function it_knows_if_the_authenticated_user_is_subscribed_to_it()
    {
        $issue = create('App\Issue');

        $this->signIn();

        $issue->subscribe();

        $this->assertTrue($issue->isSubscribedTo);
    }

}
