<?php
namespace Devl0pr\RequestManagerBundle\Tests;

use Devl0pr\RequestManagerBundle\Devl0prRequestManagerBundle;
use Devl0pr\RequestManagerBundle\Request\AbstractRequestRule;
use Devl0pr\RequestManagerBundle\Request\RequestManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestManagerTest extends TestCase
{
    public function testPushAndPop(): void
    {
	    $kernel = new RequestManagerTestKernel();
		$kernel->boot();
	    $container = $kernel->getContainer();

	    $content = '{
		    "list": [1, 2, 3],
		    "objects": {
		        "name": "Emil",
		        "surname": "Manafov"
		    },
		    "nestedArray": [
		        {
		            "name": "Emil",
		            "surname": "Manafov",
		            "ages": [18, 20]
		        },
		        {
		            "name": "Emil",
		            "surname": "Manafov",
		            "ages": [21, 18, 34]
		        }
		    ]
		}';

	    $request = Request::create('/', 'GET', [],  [], [], [], $content);
	    $request->headers->set('Content-type', 'application/json');

	    /**
	     * @var RequestStack $requestStack
	     */
	    $requestStack = $container->get('request_stack');
		$requestStack->push($request);

	    /**
	     * @var ValidatorInterface $validator
	     */
	    $validator = $container->get('validator');
	    /**
	     * @var PropertyAccessorInterface $propertyAccessor
	     */
	    $propertyAccessor = $container->get('property_accessor');

	    /**
	     * @var RequestManager $requestManager
	     */
	    $requestManager = new RequestManager($validator, $propertyAccessor, $requestStack, true);

		$data = $requestManager->validate(new SomeRequestRule());

		$this->assertIsArray($data);
    }
}


class RequestManagerTestKernel extends Kernel
{
	public function __construct()
	{
		parent::__construct('test', true);
	}

	public function registerBundles(): iterable
	{
		return [
			new Devl0prRequestManagerBundle()
		];
	}

	public function registerContainerConfiguration(LoaderInterface $loader)
	{
		// TODO: Implement registerContainerConfiguration() method.
	}
}

class SomeRequestRule extends AbstractRequestRule
{
	public function getValidationMap() : array
	{
		return [
			'list' => [
				'constraints' => [
					new NotBlank(),
					new Type('array'),
					new All(
						[
							'constraints' => [
								new Type("numeric")
							]
						]
					)
				],
			],
			'objects' => [
				'constraints' => [
					new NotBlank(),
					new Collection(
						[
							'name' => [
								new NotBlank(),
							],
							'surname' => [
								new NotBlank(),
								new Length(['min' => 3]),
							]
						]
					),
				]
			],
			"nestedArray" => [
				'constraints' => [
					new NotBlank(),
					new Type('array'),
					new All(
						[
							'constraints' => [
								new Collection(
									[
										'name' => [
											new NotBlank(),
										],
										'surname' => [
											new NotBlank(),
											new Length(['min' => 3])
										],
										'ages' => [
											new NotBlank(),
											new Type('array'),
											new All(
												[
													'constraints' => [
														new Type('int')
													]
												]
											)
										]
									]
								)
							]
						]
					),
				]
			]
		];
	}
}