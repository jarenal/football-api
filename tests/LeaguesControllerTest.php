<?php

namespace JarenalBundle\Tests\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use JarenalBundle\Entity\League;
use JarenalBundle\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LeaguesControllerTest extends WebTestCase
{
    private $validToken;
    private $invalidToken;

    protected function setUp()
    {
        // environment variables set at phpunit.xml
        $this->validToken = getenv("VALID_TOKEN");
        $this->invalidToken = getenv("INVALID_TOKEN");
        parent::setUp();
    }

    public function testGetSuccess()
    {
        $client = static::createClient();

        $fakeTeam = new Team();
        $fakeTeam->setId('222');
        $fakeTeam->setName('Real Madrid C.F.');
        $fakeTeam->setStrip(['white']);

        $fakeLeague = new League();
        $fakeLeague->setId('111');
        $fakeLeague->setName('LaLiga Santander');
        $fakeLeague->addTeam($fakeTeam);

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willReturn($fakeLeague);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request('GET', '/api/leagues/111', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]);

        $response = json_decode($client->getResponse()->getContent(), true);

        $expected = [
            'id' => '111',
            'name' => 'LaLiga Santander',
            'teams' =>
                [
                    [
                        'id' => '222',
                        'name' => 'Real Madrid C.F.',
                        'strip' =>
                            [
                                0 => 'white',
                            ],
                    ]
                ]
        ];

        $this->assertEquals($expected, $response);

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testGetInvalidToken()
    {
        $client = static::createClient();

        $client->request('GET', '/api/leagues/111', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->invalidToken]);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('Forbidden', $response['error']['message']);

        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testGetNotFound()
    {
        $client = static::createClient();

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willReturn(null);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request('GET', '/api/leagues/333', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("The given league doesn't exist", $response["message"]);

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testGetUnexpectedError()
    {
        $client = static::createClient();

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willThrowException(new \Exception('Unexpected error!'));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request('GET', '/api/leagues/111', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("Something was wrong", $response["message"]);

        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testDeleteSuccess()
    {
        $client = static::createClient();

        $fakeTeam = new Team();
        $fakeTeam->setId('222');
        $fakeTeam->setName('Real Madrid C.F.');
        $fakeTeam->setStrip(['white']);

        $fakeLeague = new League();
        $fakeLeague->setId('111');
        $fakeLeague->setName('LaLiga Santander');
        $fakeLeague->addTeam($fakeTeam);

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willReturn($fakeLeague);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $objectManager->expects($this->any())
            ->method('remove')
            ->willReturn(true);

        $objectManager->expects($this->any())
            ->method('flush')
            ->willReturn(true);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request('DELETE', '/api/leagues/111', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]);

        $this->assertEquals(
            204,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testDeleteInvalidToken()
    {
        $client = static::createClient();

        $client->request(
            'DELETE',
            '/api/leagues/111',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->invalidToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('Forbidden', $response['error']['message']);

        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testDeleteNotFound()
    {
        $client = static::createClient();

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willReturn(null);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request('DELETE', '/api/leagues/333', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("The given league doesn't exist", $response["message"]);

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testDeleteUnexpectedError()
    {
        $client = static::createClient();

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willThrowException(new \Exception('Unexpected error!'));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request('DELETE', '/api/leagues/111', [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("Something was wrong", $response["message"]);

        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPostSuccess()
    {
        $client = static::createClient();

        $fakeLeague = new League();
        $fakeLeague->setId('111');
        $fakeLeague->setName('LaLiga Santander');

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willReturn($fakeLeague);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $objectManager->expects($this->any())
            ->method('persist')
            ->willReturn(true);

        $objectManager->expects($this->any())
            ->method('flush')
            ->willReturn(true);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request(
            'POST',
            '/api/leagues/111/teams',
            ['name' => 'Real Madrid', 'strip' => ['white']],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("Team created successfully", $response["message"]);

        $this->assertEquals(
            201,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPostInvalidToken()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/leagues/111/teams',
            ['name' => 'Real Madrid', 'strip' => ['white']],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->invalidToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('Forbidden', $response['error']['message']);

        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPostNameRequired()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/leagues/111/teams',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("name is required", $response["message"]);

        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPostStripRequired()
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/leagues/111/teams',
            ['name' => 'Real Madrid'],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("strip is required", $response["message"]);

        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPostLeagueNotExist()
    {
        $client = static::createClient();

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willReturn(null);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request(
            'POST',
            '/api/leagues/333/teams',
            ['name' => 'Real Madrid', 'strip' => ['white']],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("The given league doesn't exist", $response["message"]);

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPostUnexpectedError()
    {
        $client = static::createClient();

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willThrowException(new \Exception('Unexpected error!'));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request(
            'POST',
            '/api/leagues/111/teams',
            ['name' => 'Real Madrid',
                'strip' => ['white']],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("Something was wrong", $response["message"]);

        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPutSuccess()
    {
        $client = static::createClient();

        $fakeTeam = new Team();
        $fakeTeam->setId('222');
        $fakeTeam->setName('Real Madrid C.F.');
        $fakeTeam->setStrip(['white']);

        $fakeLeague = new League();
        $fakeLeague->setId('111');
        $fakeLeague->setName('LaLiga Santander');

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willReturnOnConsecutiveCalls($fakeLeague, $fakeTeam);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $objectManager->expects($this->any())
            ->method('persist')
            ->willReturn(true);

        $objectManager->expects($this->any())
            ->method('flush')
            ->willReturn(true);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request(
            'PUT',
            '/api/leagues/111/teams/222',
            ['name' => 'Real Madrid', 'strip' => ['white']],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $this->assertEquals(
            204,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPutInvalidToken()
    {
        $client = static::createClient();

        $client->request(
            'PUT',
            '/api/leagues/111/teams/222',
            ['name' => 'Real Madrid', 'strip' => ['white']],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->invalidToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('Forbidden', $response['error']['message']);

        $this->assertEquals(
            403,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPutNameRequired()
    {
        $client = static::createClient();

        $client->request(
            'PUT',
            '/api/leagues/111/teams/222',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("name is required", $response["message"]);

        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPutStripRequired()
    {
        $client = static::createClient();

        $client->request(
            'PUT',
            '/api/leagues/111/teams/222',
            ['name' => 'FC Barcelona'],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("strip is required", $response["message"]);

        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPutLeagueNotExist()
    {
        $client = static::createClient();

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willReturn(null);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request(
            'PUT',
            '/api/leagues/333/teams/222',
            ['name' => 'FC Barcelona', 'strip' => ['red', 'blue']],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("The given league doesn't exist", $response["message"]);

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPutTeamNotExist()
    {
        $client = static::createClient();

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willReturnOnConsecutiveCalls([true, null]);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request(
            'PUT',
            '/api/leagues/111/teams/333',
            ['name' => 'FC Barcelona', 'strip' => ['red', 'blue']],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("The given team doesn't exist", $response["message"]);

        $this->assertEquals(
            404,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testPutUnexpectedError()
    {
        $client = static::createClient();

        $leagueRepository = $this->createMock(ObjectRepository::class);
        $leagueRepository->expects($this->any())
            ->method('find')
            ->willThrowException(new \Exception('Unexpected error!'));

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($leagueRepository);

        $container = $client->getContainer();

        $container->set('doctrine.orm.default_entity_manager', $objectManager);

        $client->request(
            'PUT',
            '/api/leagues/111/teams/222',
            ['name' => 'FC Barcelona', 'strip' => ['red', 'blue']],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->validToken]
        );

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals("Something was wrong", $response["message"]);

        $this->assertEquals(
            500,
            $client->getResponse()->getStatusCode()
        );
    }
}
