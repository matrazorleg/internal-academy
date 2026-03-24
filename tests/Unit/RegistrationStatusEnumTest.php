<?php

namespace Tests\Unit;

use App\Enums\RegistrationStatus;
use PHPUnit\Framework\TestCase;

class RegistrationStatusEnumTest extends TestCase
{
    public function test_registration_status_enum_values_are_stable(): void
    {
        $this->assertSame('confirmed', RegistrationStatus::Confirmed->value);
        $this->assertSame('waiting', RegistrationStatus::Waiting->value);
    }
}
