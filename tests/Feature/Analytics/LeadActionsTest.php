<?php

use App\Enums\LeadStatus;
use App\Models\Clinic;
use App\Models\ClinicLead;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->lead = ClinicLead::factory()->status(LeadStatus::New)->create();
});

it('lets admins move a lead to another status', function () {
    $this->actingAs($this->admin)
        ->from(route('leads.index'))
        ->patch(route('leads.update', $this->lead), ['status' => 'converted'])
        ->assertRedirect(route('leads.index'))
        ->assertInertiaFlash('toast.type', 'success');

    expect($this->lead->fresh()->status)->toBe(LeadStatus::Converted);
});

it('rejects an unknown status', function () {
    $this->actingAs($this->admin)
        ->patch(route('leads.update', $this->lead), ['status' => 'archived'])
        ->assertSessionHasErrors('status');

    expect($this->lead->fresh()->status)->toBe(LeadStatus::New);
});

it('forbids non-admins from changing leads', function () {
    $this->actingAs(User::factory()->create())
        ->patch(route('leads.update', $this->lead), ['status' => 'lost'])
        ->assertForbidden();

    expect($this->lead->fresh()->status)->toBe(LeadStatus::New);
});

it('exports the filtered leads as CSV', function () {
    $clinic = Clinic::factory()->create(['name' => 'Paw Print Veterinary Clinic', 'city' => 'Makati']);
    ClinicLead::factory()->for($clinic)->status(LeadStatus::Converted)->create(['occurred_at' => now()->subDay()]);

    $response = $this->actingAs($this->admin)->get(route('leads.export', ['status' => 'converted']));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertDownload('clinic-leads-'.now()->format('Y-m-d').'.csv');

    $lines = array_values(array_filter(explode("\n", $response->streamedContent())));

    expect($lines[0])->toBe('id,occurred_at,clinic,city,country,action,status')
        ->and($lines)->toHaveCount(2)
        ->and($lines[1])->toContain('"Paw Print Veterinary Clinic",Makati,PH,')
        ->and($lines[1])->toEndWith(',converted');
});

it('neutralises spreadsheet formulas in exported text', function () {
    $clinic = Clinic::factory()->create(['name' => '=HYPERLINK("http://evil.test")']);
    ClinicLead::factory()->for($clinic)->create(['occurred_at' => now()->subDay()]);

    $content = $this->actingAs($this->admin)->get(route('leads.export'))->streamedContent();

    expect($content)->toContain("'=HYPERLINK");
});

it('forbids non-admins from exporting', function () {
    $this->actingAs(User::factory()->create())->get(route('leads.export'))->assertForbidden();
});
