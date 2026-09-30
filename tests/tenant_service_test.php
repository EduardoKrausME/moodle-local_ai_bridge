<?php
namespace local_ai_bridge;

defined('MOODLE_INTERNAL') || die();

final class tenant_service_test extends \advanced_testcase {
    public function test_profile_conditions(): void {
        $this->assertSame(['institution' => 'University'], tenant_service::profile_conditions('institution', 'University', 'Math'));
        $this->assertSame(['department' => 'Math'], tenant_service::profile_conditions('department', 'University', 'Math'));
        $this->assertSame(['institution' => 'University', 'department' => 'Math'], tenant_service::profile_conditions('institution_department', 'University', 'Math'));
        $this->assertNull(tenant_service::profile_conditions('institution_department', 'University', ''));
    }
}
