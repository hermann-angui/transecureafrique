<?php

namespace App\Command;

use App\Entity\Demande;
use App\Entity\Macaron;
use App\Entity\Payment;
use App\Helper\CsvReaderHelper;
use App\Helper\PasswordHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'make:fake',
    description: 'Add a short description for your command',
)]
class GenerateFakeDataCommand extends Command
{

    /**
     * @var CsvReaderHelper
     */
    private $csvReaderHelper;

    public function __construct(EntityManagerInterface $entityManager, CsvReaderHelper $csvReaderHelper)
    {
        $this->entityManager = $entityManager;
        $this->csvReaderHelper = $csvReaderHelper;
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->addArgument('from', InputArgument::OPTIONAL, 'Initial matricule')
            ->addOption('to', null, InputOption::VALUE_NONE, 'Final matricule')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fichier = "/var/www/html/tmp/demande.csv";
        $data = $this->csvReaderHelper->read($fichier);
        foreach($data as $row){
            $demande = new Demande();
            $demande->setReference($row["Reference"]);
            $demande->setMontant($row["Montant"]);
           // $demande->setType("MACARON");
            $demande->setCylindree($row["Cylindree"]);
            $demande->setDateRendezVous($row["DateRendezVous"]);
            $demande->setNumeroRecepisse($row["NumeroRecepisse"]);
            $demande->setNumeroCarteGrise($row["NumeroCarteGrise"]);
            $demande->setDateDEdition($row["DateDEdition"]);
            $demande->setNumeroDImmatriculationPrecedent($row["NumeroDImmatriculationPrecedent"]);
            $demande->setTypeTechnique($row["TypeTechnique"]);
            $demande->setCouleurVehicule($row["CouleurVehicule"]);
            $demande->setSocieteDeCredit($row["SocieteDeCredit"]);
            $demande->setNumeroVinChassis($row["NumeroVinChassis"]);
            $demande->setNombreDEssieux($row["NombreDEssieux"]);
            $demande->setPlacesAssises($row["PlacesAssises"]);
            $demande->setUsageVehicule($row["UsageVehicule"]);
            $demande->setEnergieVehicule($row["EnergieVehicule"]);
            $demande->setMarqueDuVehicule($row["MarqueDuVehicule"]);
            $demande->setCarroserieVehicule($row["CarroserieVehicule"]);
            $demande->setIdentiteProprietaire($row["IdentiteProprietaire"]);
            $demande->setTypeCommercial($row["TypeCommercial"]);
            $demande->setIdentiteProprietairePiece($row["IdentiteProprietairePiece"]);
            $demande->setDateDePremiereMiseEnCirulation($row["DateDePremiereMiseEnCirulation"]);
            $demande->setPuissanceFiscale($row["PuissanceFiscale"]);
            $demande->setStatus($row["Status"]);
            $this->entityManager->persist($demande);

            $payment = new Payment();
            $payment->setCreatedAt(new \DateTime());
            $payment->setStatus("COMPLETED");
            $payment->setCodePaymentOperateur(PasswordHelper::generate(10));
            $payment->setOperateur("WAVE");
            $payment->setReference(PasswordHelper::generate(10));
            $payment->setType("MOBILE_MONEY");
            $payment->setDemande($demande);
            $this->entityManager->persist($payment);

            $macaron = new Macaron();
            $macaron->setStatus("WITHDRAW");
            $macaron->setReference(PasswordHelper::generate(10));
            $macaron->setMacaronQrcodeNumber(PasswordHelper::generate(10));
            $macaron->setValidityFrom(new \DateTime());
            $macaron->setValidityTo(new \DateTime("now"));
            $macaron->setDemande($demande);
            $this->entityManager->persist($macaron);

        }

        return Command::SUCCESS;
    }

}


// "Reference";"Montant";"Type";"Cylindree";"DateRendezVous";"NumeroRecepisse";"NumeroCarteGrise";"DateDEdition";"NumeroDImmatriculationPrecedent";"TypeTechnique";"CouleurVehicule";"SocieteDeCredit";"NumeroVinChassis";"NombreDEssieux";"PlacesAssises";"UsageVehicule";"EnergieVehicule";"MarqueDuVehicule";"CarroserieVehicule";"IdentiteProprietaire";"TypeCommercial";"IdentiteProprietairePiece""DateDePremiereMiseEnCirulation";"PuissanceFiscale";"Status"
