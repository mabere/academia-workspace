<?php

namespace Tests\Unit;

use App\Services\Bkd\BkdCalculator;
use PHPUnit\Framework\TestCase;

class BkdCalculatorTest extends TestCase
{
    private BkdCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new BkdCalculator;
    }

    public function test_it_calculates_correct_sks_for_evidence_types(): void
    {
        $this->assertEquals(2.0, $this->calculator->calculateItemSks('writewise_assignment', 'published'));
        $this->assertEquals(0.5, $this->calculator->calculateItemSks('writewise_assignment', 'draft'));
        $this->assertEquals(1.0, $this->calculator->calculateItemSks('writewise_feedback', 'published'));
        $this->assertEquals(1.0, $this->calculator->calculateItemSks('literature_source', 'verified'));
        $this->assertEquals(1.5, $this->calculator->calculateItemSks('instrument_blueprint', 'reviewed'));
        $this->assertEquals(2.0, $this->calculator->calculateItemSks('expert_validation', 'expert_review'));
        $this->assertEquals(2.0, $this->calculator->calculateItemSks('corpuslab_protocol', 'approved'));
        $this->assertEquals(1.5, $this->calculator->calculateItemSks('gurubahasa_material', 'curated'));
    }

    public function test_it_maps_correct_tri_dharma_pillars(): void
    {
        $this->assertEquals('teaching', $this->calculator->determinePillar('writewise_assignment'));
        $this->assertEquals('teaching', $this->calculator->determinePillar('writewise_feedback'));
        $this->assertEquals('research', $this->calculator->determinePillar('literature_source'));
        $this->assertEquals('research', $this->calculator->determinePillar('instrument_blueprint'));
        $this->assertEquals('research', $this->calculator->determinePillar('corpuslab_protocol'));
        $this->assertEquals('community', $this->calculator->determinePillar('gurubahasa_material'));

        $this->assertEquals('Pendidikan', $this->calculator->pillarLabel('teaching'));
        $this->assertEquals('Penelitian', $this->calculator->pillarLabel('research'));
        $this->assertEquals('Pengabdian', $this->calculator->pillarLabel('community'));
    }

    public function test_it_calculates_summary_eligibility_correctly(): void
    {
        $items = collect([
            ['pillar' => 'teaching', 'sks' => 4.0],
            ['pillar' => 'research', 'sks' => 6.0],
            ['pillar' => 'community', 'sks' => 2.0],
        ]);

        $summary = $this->calculator->calculateSummary($items);

        $this->assertEquals(4.0, $summary['teaching_sks']);
        $this->assertEquals(6.0, $summary['research_sks']);
        $this->assertEquals(2.0, $summary['community_sks']);
        $this->assertEquals(12.0, $summary['total_sks']);
        $this->assertTrue($summary['teaching_met']);
        $this->assertTrue($summary['research_met']);
        $this->assertTrue($summary['community_met']);
        $this->assertTrue($summary['is_eligible']);
        $this->assertEquals('MEMENUHI (M)', $summary['status_label']);
    }

    public function test_it_marks_ineligible_when_below_minimum(): void
    {
        $items = collect([
            ['pillar' => 'teaching', 'sks' => 2.0], // kurang dari 3.0
            ['pillar' => 'research', 'sks' => 2.0],
            ['pillar' => 'community', 'sks' => 1.0],
        ]);

        $summary = $this->calculator->calculateSummary($items);

        $this->assertEquals(5.0, $summary['total_sks']);
        $this->assertFalse($summary['teaching_met']);
        $this->assertFalse($summary['is_eligible']);
        $this->assertEquals('BELUM MEMENUHI (TM)', $summary['status_label']);
    }
}
