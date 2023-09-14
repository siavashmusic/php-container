<?php


use PHPUnit\Framework\TestCase;
use Siavash\Container\Container;

#[AllowDynamicProperties]
class ContainerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container;
    }

    public function test_it_allows_you_to_register_services_using_closures(): void
    {
        $this->container->register('service', fn () => new TestService);

        $this->assertTrue($this->container->has('service'));
        $this->assertInstanceOf(TestService::class, $this->container->get('service'));
        $this->assertNotSame($this->container->get('service'), $this->container->get('service'));
    }

    public function test_it_allows_you_to_register_services_using_name(): void
    {

        $this->container->register('service', 'some-string');

        $this->assertEquals('some-string', $this->container->get('service'));
    }

}

class TestService
{
}