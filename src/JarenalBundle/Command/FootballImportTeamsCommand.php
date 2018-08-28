<?php

namespace JarenalBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use JarenalBundle\Entity\League;
use JarenalBundle\Entity\Team;
use Doctrine\Common\Persistence\ObjectManager;

class FootballImportTeamsCommand extends ContainerAwareCommand
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('football:import-teams')
            ->setDescription('Import teams from a given CSV file')
            ->addArgument('filename', InputArgument::REQUIRED,
                'A CSV file.')//->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->truncateTables();

        $filename = $input->getArgument('filename');

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        $output->writeln("Reading filename '{$filename}'...");
        $teams = $serializer->decode(file_get_contents($filename), 'csv');

        $repository = $this->em->getRepository(League::class);

        $leagueTable = $this->em->getClassMetadata(League::class);
        $teamTable = $this->em->getClassMetadata(Team::class);
        $leagueTable->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
        $teamTable->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

        foreach ($teams as $csvTeam) {
            $league = $repository->findOneByName($csvTeam["League"]);

            if (!$league) {
                $league = new League();
                $league->setId($csvTeam["League_id"]);
                $league->setName($csvTeam["League"]);
                $this->em->persist($league);
                $this->em->flush();
                $output->writeln("League '{$csvTeam["League"]}' created.");
            }

            $team = new Team();
            $team->setId($csvTeam["Team_id"]);
            $team->setName($csvTeam["Team"]);
            $team->setStrip($csvTeam["Strip"]);
            $team->setLeague($league);
            $this->em->persist($team);
            $this->em->flush();
            $output->writeln(" |--> Team '{$csvTeam["Team"]}' created.");
        }

        $output->writeln('All done!');
        $output->writeln('');
    }

    private function truncateTables()
    {
        echo "Cleaning tables...\n";
        $leagueTable = $this->em->getClassMetadata(League::class);
        $teamTable = $this->em->getClassMetadata(Team::class);
        $conn = $this->em->getConnection();
        $databasePlatform = $conn->getDatabasePlatform();
        $conn->beginTransaction();
        try {
            $conn->query('SET FOREIGN_KEY_CHECKS=0');
            $q1 = $databasePlatform->getTruncateTableSql($leagueTable->getTableName());
            $q2 = $databasePlatform->getTruncateTableSql($teamTable->getTableName());
            $conn->executeUpdate($q1);
            $conn->executeUpdate($q2);
            $conn->query('SET FOREIGN_KEY_CHECKS=1');
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollback();
        }
    }

}
