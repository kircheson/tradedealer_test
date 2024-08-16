<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Car;
use App\Entity\Brand;
use App\Entity\Model;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ApiControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $brand = new Brand();
        $brand->setName('Test Brand');
        $this->entityManager->persist($brand);

        $model = new Model();
        $model->setName('Test Model');
        $model->setBrand($brand);
        $this->entityManager->persist($model);

        $car = new Car();
        $car->setBrand($brand);
        $car->setModel($model);
        $car->setPhoto('test.jpg');
        $car->setPrice(1000000);
        $this->entityManager->persist($car);

        $this->entityManager->flush();
    }

    public function testGetCarsList()
    {
        $this->client->request('GET', '/api/v1/cars');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);

        foreach ($responseData as $car) {
            $this->assertArrayHasKey('id', $car);
            $this->assertArrayHasKey('brand', $car);
            $this->assertArrayHasKey('photo', $car);
            $this->assertArrayHasKey('price', $car);

            $this->assertIsInt($car['id']);
            $this->assertIsArray($car['brand']);
            $this->assertArrayHasKey('id', $car['brand']);
            $this->assertArrayHasKey('name', $car['brand']);
            $this->assertIsString($car['photo']);
            $this->assertIsInt($car['price']);
        }
    }

    public function testGetCarsListWithPagination()
    {
        $this->client->request('GET', '/api/v1/cars?page=1&limit=10');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('items', $responseData);
        $this->assertArrayHasKey('total', $responseData);
        $this->assertArrayHasKey('page', $responseData);
        $this->assertArrayHasKey('limit', $responseData);

        $this->assertLessThanOrEqual(10, count($responseData['items']));
        $this->assertEquals(1, $responseData['page']);
        $this->assertEquals(10, $responseData['limit']);
    }

    public function testGetSingleCar()
    {
        $car = $this->entityManager->getRepository(Car::class)->findOneBy([]);
        $this->client->request('GET', '/api/v1/cars/' . $car->getId());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('model', $responseData);
        $this->assertArrayHasKey('photo', $responseData);
        $this->assertArrayHasKey('price', $responseData);

        $this->assertIsInt($responseData['id']);
        $this->assertIsArray($responseData['brand']);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('name', $responseData['brand']);
        $this->assertIsArray($responseData['model']);
        $this->assertArrayHasKey('id', $responseData['model']);
        $this->assertArrayHasKey('name', $responseData['model']);
        $this->assertIsString($responseData['photo']);
        $this->assertIsInt($responseData['price']);
    }

    public function testGetNonExistentCar()
    {
        $this->client->request('GET', '/api/v1/cars/9999');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider creditCalculationProvider
     */
    public function testCalculateCredit($price, $initialPayment, $loanTerm, $expectedStatus)
    {
        $this->client->request('GET', "/api/v1/credit/calculate?price={$price}&initialPayment={$initialPayment}&loanTerm={$loanTerm}");
        $this->assertEquals($expectedStatus, $this->client->getResponse()->getStatusCode());

        if ($expectedStatus === 200) {
            $responseData = json_decode($this->client->getResponse()->getContent(), true);

            $this->assertArrayHasKey('programId', $responseData);
            $this->assertArrayHasKey('interestRate', $responseData);
            $this->assertArrayHasKey('monthlyPayment', $responseData);
            $this->assertArrayHasKey('title', $responseData);

            $this->assertIsInt($responseData['programId']);
            $this->assertIsNumeric($responseData['interestRate']);
            $this->assertIsInt($responseData['monthlyPayment']);
            $this->assertIsString($responseData['title']);
        }
    }

    public function creditCalculationProvider()
    {
        return [
            [1000000, 200000, 36, 200],
            [500000, 100000, 24, 200],
            [2000000, 400000, 60, 200],
            [-1000, 1000, 12, 400],
            [1000000, -10000, 36, 400],
            [1000000, 200000, 0, 400],
        ];
    }

    public function testSaveRequest()
    {
        $car = $this->entityManager->getRepository(Car::class)->findOneBy([]);

        $this->client->request(
            'POST',
            '/api/v1/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'carId' => $car->getId(),
                'programId' => 1,
                'initialPayment' => 200000,
                'loanTerm' => 64
            ])
        );

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('success', $responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertTrue($responseData['success']);

        // Проверка, что запрос действительно сохранен в базе данных
        $savedRequest = $this->entityManager->getRepository('App:CreditRequest')->findOneBy([
            'car' => $car->getId(),
            'programId' => 1,
            'initialPayment' => 200000,
            'loanTerm' => 64
        ]);

        $this->assertNotNull($savedRequest);
    }

    public function testSaveRequestWithInvalidData()
    {
        $this->client->request(
            'POST',
            '/api/v1/request',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'carId' => 9999, // несуществующий ID
                'programId' => 1,
                'initialPayment' => 200000,
                'loanTerm' => 64
            ])
        );

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Очистка базы данных после тестов
        $this->entityManager->createQuery('DELETE FROM App:Car')->execute();
        $this->entityManager->createQuery('DELETE FROM App:Model')->execute();
        $this->entityManager->createQuery('DELETE FROM App:Brand')->execute();
        $this->entityManager->createQuery('DELETE FROM App:CreditRequest')->execute();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
