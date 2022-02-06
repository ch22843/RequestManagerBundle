<?php

namespace Devl0pr\RequestManagerBundle\Tests;

use Devl0pr\RequestManagerBundle\Devl0prRequestManagerBundle;
use Devl0pr\RequestManagerBundle\Exception\SmartProblemException;
use Devl0pr\RequestManagerBundle\Problem\SmartProblem;
use Devl0pr\RequestManagerBundle\Request\AbstractRequestRule;
use Devl0pr\RequestManagerBundle\Request\RequestManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
    private ?ValidatorInterface $validator;
    private ?PropertyAccessorInterface $propertyAccessor;
    private ?RequestStack $requestStack;
    /**
     * @var string[]
     */

    protected function setUp(): void
    {
        $kernel = new RequestManagerTestKernel();
        $kernel->boot();
        $container = $kernel->getContainer();

        $this->requestStack = $container->get('request_stack');

        $this->validator = $container->get('validator');

        $this->propertyAccessor = $container->get('property_accessor');

        global $databaseInvoice;

        $databaseInvoice = ['1' => 'A', '2' => 'B'];
    }

    public function testWrongJsonInDebugTrue(): void
    {
        $this->expectException(SmartProblemException::class);

        $content = '{
            "name": "Javid",
            "surname": "Huseynov"
        }';

        $request = Request::create('/', 'GET', [], [], [], [], $content);
        $request->headers->set('Content-type', 'application/json');

        $this->requestStack->push($request);

        $requestManager = new RequestManager($this->validator, $this->propertyAccessor, $this->requestStack, true);

        $requestManager->validate(new SomeRequestRule());
    }

    public function testWrongJsonDebugModeFalse(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $request = $this->getRequest('simple');

        $this->requestStack->push($request);

        $requestManager = new RequestManager($this->validator, $this->propertyAccessor, $this->requestStack, false);

        $requestManager->validate(new SomeRequestRule());
    }

    public function testComplexRequest(): void
    {
        $request = $this->getRequest('complex');

        $this->requestStack->push($request);

        $requestManager = new RequestManager($this->validator, $this->propertyAccessor, $this->requestStack, true);

        $data = $requestManager->validate(new SomeRequestRule());

        $this->assertIsArray($data);
    }

    public function testBag(): void
    {
        $request = $this->getRequest('simple');

        $this->requestStack->push($request);

        $requestManager = new RequestManager($this->validator, $this->propertyAccessor, $this->requestStack, false);

        $requestManager->addToBag('somekey', 'somevalue');

        $this->assertEquals('somevalue', $requestManager->getFromBag('somekey'));
    }

    public function testManipulation(): void
    {
        $request = $this->getRequest('simple');

        $this->requestStack->push($request);

        $requestManager = new RequestManager($this->validator, $this->propertyAccessor, $this->requestStack, true);

        $data = $requestManager->validate(new SimpleRequestRule());

        $this->assertEquals('Manipulated', $data['name']);
    }

    public function testWithoutRegisterCallbackBeforeDispatchCustomValidation(): void
    {
        $this->expectException(SmartProblemException::class);

        $request = $this->getRequest('simple', 'PUT');

        $this->requestStack->push($request);

        $requestManager = new RequestManager($this->validator, $this->propertyAccessor, $this->requestStack, true);

        $data = $requestManager->validate(new SimpleRequestRule());

        $this->assertEquals(1, $data['invoice']);
    }

    public function testRegisterCallbackBeforeDispatchCustomValidation(): void
    {
        $request = $this->getRequest('simple', 'PUT');

        $this->requestStack->push($request);

        $requestManager = new RequestManager($this->validator, $this->propertyAccessor, $this->requestStack, true);

        $requestManager->registerCallbackBeforeDispatch('name', function () {
            global $databaseInvoice;
            unset($databaseInvoice[1]);
        });

        $data = $requestManager->validate(new SimpleRequestRule());

        $this->assertEquals(1, $data['invoice']);
    }


    private function getRequest($type, $method = 'GET') {

        switch ($type) {
            case 'simple':
                $content = '{
                    "name": "Javid",
                    "surname": "Huseynov",
                    "invoice": 1
                }';
                break;
            case 'complex':
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
                break;
        }

        $request = Request::create('/', $method, [], [], [], [], $content);
        $request->headers->set('Content-type', 'application/json');

        return $request;
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
            new Devl0prRequestManagerBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // TODO: Implement registerContainerConfiguration() method.
    }
}

class SimpleRequestRule extends AbstractRequestRule
{
    public function getValidationMap(): array
    {
        return [
            'name' => [
                'constraints' => [
                    new NotBlank(),
                ],
            ],
            'surname' => [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 4]),
                ]
            ],
            'invoice' => [
                'constraints' => [
                    new NotBlank(),
                ]
            ],
        ];
    }

    public function nameValidation(RequestManager $requestManager)
    {

        $data = $requestManager->getRequestContent();

        if($data['name'] == 'Javid') {
            $requestManager->manipulate('name', 'Manipulated');
        }
    }

    public function invoiceValidation(RequestManager $requestManager)
    {
        global $databaseInvoice;

        $request = $requestManager->getRequest();

        $data = $requestManager->getRequestContent();
        $invoice = $data['invoice'];

        if($request->getMethod() == 'PUT') {
            if(array_key_exists($invoice, $databaseInvoice)) {
                throw new SmartProblemException(new SmartProblem(400, 'invoice is already exists'));
            }
        }
    }
}

class SomeRequestRule extends AbstractRequestRule
{
    public function getValidationMap(): array
    {
        return [
            'list' => [
                'constraints' => [
                    new NotBlank(),
                    new Type('array'),
                    new All(
                        [
                            'constraints' => [
                                new Type("numeric"),
                            ],
                        ]
                    ),
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
                            ],
                        ]
                    ),
                ],
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
                                            new Length(['min' => 3]),
                                        ],
                                        'ages' => [
                                            new NotBlank(),
                                            new Type('array'),
                                            new All(
                                                [
                                                    'constraints' => [
                                                        new Type('int'),
                                                    ],
                                                ]
                                            ),
                                        ],
                                    ]
                                ),
                            ],
                        ]
                    ),
                ],
            ],
        ];
    }
}

