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

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function test_it_allows_you_to_register_services_using_name(): void
    {

        $this->container->register('service', 'some-string');

        $this->assertEquals('some-string', $this->container->get('service'));
    }

    public function test_it_persists_the_container_instance(): void
    {
        $firstInstance = Container::getInstance();
        $secondInstance = Container::getInstance();

        $this->assertSame($firstInstance, $secondInstance);
    }
    public function test_it_persists_services_between_instances(): void
    {
        Container::getInstance()->register('service', fn () => new TestService);

        $this->assertInstanceOf(TestService::class, Container::getInstance()->get('service'));
    }

}

class TestService
{
}