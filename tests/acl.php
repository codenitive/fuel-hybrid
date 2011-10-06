<?php

namespace Hybrid;

class Test_Acl extends \Fuel\Core\TestCase {
    
    private $enable_test = true;

    public function setup()
    {
        \Package::load('hybrid');

        $acl = \Hybrid\Acl::forge('mock');

        $acl->add_roles('guest');
        $acl->add_resources(array('blog', 'forum', 'news'));
        $acl->allow('guest', array('blog'), 'view');
        $acl->deny('guest', 'forum');
        $user_table = \DB::list_tables('users');

        if(empty($user_table))
        {
            $this->enable_test = false;
        }
    }

    public function test_access()
    {
        if ( ! $this->enable_test)
        {
            return;
        }

        $acl = \Hybrid\Acl::instance('mock');

        $expected = true;
        $output = $acl->access('blog', 'view');
        $this->assertEquals($expected, $output);

        $expected = false;
        $output = $acl->access('blog', 'edit');
        $this->assertEquals($expected, $output);

        $expected = false;
        $output = $acl->access('forum', 'view');
        $this->assertEquals($expected, $output);

        $expected = false;
        $output = $acl->access('news', 'view');
        $this->assertEquals($expected, $output);
    }

    public function test_status()
    {
        if ( ! $this->enable_test)
        {
            return;
        }
        
        $acl = \Hybrid\Acl::instance('mock');

        $expected = 200;
        $output = $acl->access_status('blog', 'view');
        $this->assertEquals($expected, $output);

        $expected = 401;
        $output = $acl->access_status('blog', 'edit');
        $this->assertEquals($expected, $output);

        $expected = 401;
        $output = $acl->access_status('forum', 'view');
        $this->assertEquals($expected, $output);

        $expected = 401;
        $output = $acl->access_status('news', 'view');
        $this->assertEquals($expected, $output);
    }
    
}