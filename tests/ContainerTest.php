<?php


use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;
use Siavash\Container\Container;
use Siavash\Container\Exceptions\CouldNotResolveAbstraction;

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
        $this->container->register('service', fn() => new TestService);

        $this->assertTrue($this->container->has('service'));
        $this->assertInstanceOf(TestService::class, $this->container->get('service'));
        $this->assertNotSame($this->container->get('service'), $this->container->get('service'));
    }

    public function test_it_allows_you_to_register_services_using_strings(): void
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
        Container::getInstance()->register('service', fn() => new TestService);

        $this->assertInstanceOf(TestService::class, Container::getInstance()->get('service'));
    }

    public function test_it_fetches_unregistered_services(): void
    {
        $this->assertInstanceOf(ORM::class, Container::getInstance()->get(ORM::class));
    }

    public function test_it_throws_an_exception_if_we_pass_a_service_that_does_not_exist(): void
    {
        $this->expectException(NotFoundExceptionInterface::class);

        Container::getInstance()->get(MyClassDoesNotExist::class);
    }

    public function test_it_inject_dependencies(): void
    {
        $user = Container::getInstance()->get(User::class);
        $this->assertInstanceOf(ORM::class, $user->orm);
    }

    public function test_it_inject_multiple_dependencies(): void
    {
        $userBuilder = Container::getInstance()->get(UserBuilder::class);
        $this->assertInstanceOf(ORM::class, $userBuilder->orm);
        $this->assertInstanceOf(TestService::class, $userBuilder->testService);

    }


    public function test_it_injects_nested_dependencies(): void
    {
        $action = Container::getInstance()->get(CreateUserAccount::class);

        $this->assertInstanceOf(User::class, $action->user);
        $this->assertInstanceOf(ORM::class, $action->user->orm);
        $this->assertInstanceOf(TestService::class, $action->service);
    }

    /**
     * @dataProvider singletonsTestProvider
     */
    public function test_it_allows_us_to_register_singletons(string $key, \Closure $service): void
    {
        $this->container->singleton($key, $service);

        $this->assertSame(
            $this->container->get($key),
            $this->container->get($key)
        );
    }

    public static function singletonsTestProvider(): array
    {
        return [
            ['service', fn () => new TestService()],
            [TestService::class, fn () => new TestService()],
            ['some-string', fn () => (object)['foo' => 'bar']],
        ];
    }

    public function test_it_can_bind_implementations_to_interfaces(): void
    {
        $this->container->register(MyInterface::class, MyImplementation::class);

        $this->assertInstanceOf(MyImplementation::class, $this->container->get(MyInterface::class));
    }

    public function test_it_can_bind_implementation_to_interfaces_as_singleton(): void
    {
        $this->container->singleton(MyInterface::class, MyImplementation::class);

        $this->assertInstanceOf(MyImplementation::class, $this->container->get(MyInterface::class));
    }

    /**
     * @dataProvider abstractionErrorProvider
     */
    public function test_it_throws_an_exception_if_it_tries_to_instantiate(): void
    {
        $this->expectException(CouldNotResolveAbstraction::class);
        $this->expectExceptionMessage('Could not resolve interface or abstract class [MyInterface]');

        $this->container->get(MyInterface::class);
    }

    public static function abstractionErrorProvider(): array
    {
        return [
            [MyInterface::class],
            [MyImplementationUser::class],
        ];
    }

    public function test_it_allows_closures_to_access_the_container(): void
    {
        $this->container->register('orm', fn() => new ORM);
        $this->container->register('user', fn(Container $container) => new User($container->get(ORM::class)));

        $this->assertInstanceOf(User::class, $this->container->get('user'));
        $this->assertInstanceOf(ORM::class, $this->container->get('user')->orm);

    }
}

class TestService
{
}

class ORM
{
}
class User
{
    public function __construct(
        public ORM $orm
    )
    {
    }
}

class CreateUserAccount
{
    public function __construct(
        public User $user,
        public TestService $service
    )
    {
    }
}

class UserBuilder
{
    public function __construct(
        public TestService $testService,
        public ORM $orm
    )
    {
    }
}

interface MyInterface
{
}
class MyImplementation implements MyInterface
{
}
class MyImplementationUser
{
    public function __construct(
        protected MyInterface $myInterface
    )
    {
    }
}
{
}

class MyAbstractClass
{
}