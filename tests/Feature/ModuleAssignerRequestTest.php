<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModuleAssignerRequestTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_module_assigner_request_for_non_infusion_soft_customer()
    {
        $response = $this->json('POST', '/api/module_reminder_assigner', ['contact_email' => 'test@notainfuioncustomer.com']);

        $response->assertStatus(422)->assertExactJson([
                'errors' => [
                    'status_code' => 422,
                    'status' => 'failed',
                    'message' => "The email is not a valid Infusion Soft Customer Email"
                ],
            ]);
    }

    public function test_module_assigner_request_for_invalid_param_soft_customer()
    {
        $response = $this->json('POST', '/api/module_reminder_assigner', ['invalid_param' => 'test@notainfuioncustomer.com']);

        $response->assertStatus(422)->assertExactJson([
            'errors' => [
                'status_code' => 422,
                'status' => 'failed',
                'message' => "Invalid Params"
            ],
        ]);
    }

    public function test_module_assigner_request_for_valid_customer_soft_customer()
    {
        $response = $this->json('POST', '/api/module_reminder_assigner', ['contact_email' => '5b911648a2f2c@test.com']);

        $response->assertStatus(422)->assertExactJson([
            'errors' => [
                'status_code' => 422,
                'status' => 'failed',
                'message' => "Invalid Params"
            ],
        ]);
    }
}
