<?php

use App\Libraries\IllustrationRobot;
use CodeIgniter\Test\CIUnitTestCase;

final class IllustrationRobotTest extends CIUnitTestCase
{
    public function testRobotEstUnSvgALaCouleurDemandee(): void
    {
        $svg = IllustrationRobot::robot('#00d4ff', 1);

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringContainsString('#00d4ff', $svg);
        $this->assertStringContainsString('viewBox="0 0 400 500"', $svg);
    }

    public function testLesDeuxVariantesSontDifferentes(): void
    {
        $this->assertNotSame(IllustrationRobot::robot('#00d4ff', 1), IllustrationRobot::robot('#00d4ff', 2));
    }

    public function testCouleurInvalideRefusee(): void
    {
        $this->expectException(InvalidArgumentException::class);
        IllustrationRobot::robot('red"><script>', 1);
    }

    public function testLogoContientLesInitiales(): void
    {
        $this->assertStringContainsString('>UR<', IllustrationRobot::logo('UR', '#00d4ff'));
    }
}
