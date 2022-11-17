<?php

use Jaime\Vtiger;
use PHPUnit\Framework\TestCase;

class VtigerTest extends TestCase
{
    private $vtiger;

    public function setUp(): void
    {
        $this->vtiger = new Vtiger('VTIGER_URL', 'VTIGER_USER', 'VTIGER_ACCESSKEY');
    }

    public function testCreateUpdateRetrieve()
    {
        // Create
        $result_create = $this->vtiger->create(
            [
                'assigned_user_id' => 1, 'lastname' => 'Doe', 'firstname' => 'Jhon'
            ],
            'Contacts'
        );
        $this->assertTrue($result_create->success);

        // Update
        $result = $this->vtiger->update(
            [
                'id' => $result_create->result->id, 'lastname' => 'Doe', 'firstname' => 'Pedro', 'assigned_user_id' => 1
            ]
        );
        $this->assertTrue($result->success);

        // Retrieve
        $result = $this->vtiger->retrieve($result_create->result->id);
        $this->assertTrue($result->success);

        // Revise
        $result = $this->vtiger->revise([
            'id' => $result_create->result->id,
            'lastname' => 'Perez',
            'assigned_user_id' => '19x1'
        ]);
        $this->assertTrue($result->success);
    }

    public function testDescribe()
    {
        $result = $this->vtiger->describe('Contacts');
        $this->assertTrue($result->success);
    }

    public function testQuery()
    {
        $paramsSearch = ['email' => 'test2@test.com'];
        $select = ['mobile'];

        $result = $this->vtiger->query('Contacts', $paramsSearch, $select);
        $this->assertTrue($result->success);
    }

    public function testListTypes()
    {
        $result = $this->vtiger->listTypes();
        $this->assertTrue($result->success);
    }
}
