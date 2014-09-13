<?php

class DistanceTest extends PHPUnit_Framework_TestCase
{
    public function testConvertKilometersToMiles()
    {
        $this->assertEquals(0.621371, Distance::kilometersToMiles(1));
    }

    public function testConvertKilometersToMeters()
    {
        $this->assertEquals(1000, Distance::kilometersToMeters(1));
    }

    public function testConvertKilometersToYards()
    {
        $this->assertEquals(1093.61, Distance::kilometersToYards(1));
    }

    public function testConvertMilesToKilometers()
    {
        $this->assertEquals(1.60934, Distance::milesToKilometers(1));
    }

    public function testConvertMilesToMeters()
    {
        $this->assertEquals(1609.34, Distance::milesToMeters(1));
    }

    public function testConvertMilesToYards()
    {
        $this->assertEquals(1760, Distance::milesToYards(1));
    }

    public function testConvertMetersToKilometers()
    {
        $this->assertEquals(0.001, Distance::metersToKilometers(1));
    }

    public function testConvertMetersToMiles()
    {
        $this->assertEquals(0.000621371, Distance::metersToMiles(1));
    }

    public function testConvertMetersToYards()
    {
        $this->assertEquals(1.09361, Distance::metersToYards(1));
    }

    public function testCalculateDistanceMiles()
    {
        $this->assertEquals(1003.2646776326, Distance::calculateDistance(27.947222, -82.458611, 40.67, -73.94));
    }

    public function testCalculateDistanceKilometers()
    {
        $this->assertEquals(1614.5939763012, Distance::calculateDistance(27.947222, -82.458611, 40.67, -73.94, 'kilometers'));
    }

    public function testCalculateDistanceMeters()
    {
        $this->assertEquals(1614593.9763012, Distance::calculateDistance(27.947222, -82.458611, 40.67, -73.94, 'meters'), '', 0.2);
    }

    public function testCalculateDistanceYards()
    {
        $this->assertEquals(1765745.8326334, Distance::calculateDistance(27.947222, -82.458611, 40.67, -73.94, 'yards'), '', 0.2);
    }

    public function testNotEnoughUnits()
    {
        $this->assertFalse(Distance::milesTo(123));
    }
}

