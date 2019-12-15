<?php


namespace ErpAPI\ErpAPICommand\Console\Command;


use Encomage\ErpIntegration\Helper\ErpApiClient;
use Encomage\ErpIntegration\Helper\ErpApiCustomer;
use Encomage\ErpIntegration\Helper\ErpApiInvoice;
use Encomage\ErpIntegration\Helper\StringUtils;
use Encomage\ErpIntegration\Model\Api\Invoice as ApiInvoice;
use Exception;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\OrderRepository;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function GuzzleHttp\json_decode as json_decodeAlias;

/**
 * Class InvoiceCommand
 *
 * @package ErpAPI\ErpAPICommand\Console\Command
 */
class InvoiceCommand extends Command
{

    const NAME_ARGUMENT = 'name';

    const NAME_OPTION = 'option';

    public const COMPCODE = 'erp_etoday_settings/erp_authorization/compcode';

    public const HOST_NAME = 'erp_etoday_settings/erp_authorization/host_name';

    public const LOGIN = 'erp_etoday_settings/erp_authorization/login';

    public const PASSWORD = 'erp_etoday_settings/erp_authorization/password';

    public const TEST_MODE = 'erp_etoday_settings/erp_authorization/enabled_test_mode';

    public const WAREHOUSE_CODE = 'erp_etoday_settings/erp_authorization/warehouse_code';

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;

    /**
     * @var \Encomage\ErpIntegration\Model\Api\Invoice
     */
    private $apiInvoice;

    /**
     * @var \Encomage\ErpIntegration\Helper\StringUtils
     */
    private $string;

    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiClient
     */
    private $erpApiClient;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiCustomer
     */
    private $erpApiCustomer;
    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiInvoice
     */
    private $erpApiInvoice;

    /**
     * InvoiceCommand constructor.
     *
     * @param \Magento\Framework\App\State                   $state
     * @param \Magento\Sales\Model\OrderRepository           $orderRepository
     * @param \Encomage\ErpIntegration\Model\Api\Invoice     $apiInvoice
     * @param \Encomage\ErpIntegration\Helper\ErpApiClient   $erpApiClient
     * @param \Encomage\ErpIntegration\Helper\ErpApiCustomer $erpApiCustomer
     */
    public function __construct(
        State $state,
        OrderRepository $orderRepository,
        ApiInvoice $apiInvoice,
        ErpApiClient $erpApiClient,
        ErpApiCustomer $erpApiCustomer,
        ErpApiInvoice $erpApiInvoice
    ) {
        $this->state           = $state;
        $this->orderRepository = $orderRepository;
        $this->apiInvoice      = $apiInvoice;

        $this->string         = new StringUtils();
        $this->erpApiClient   = $erpApiClient;
        $this->erpApiCustomer = $erpApiCustomer;
        $this->erpApiInvoice = $erpApiInvoice;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('erpapi:invoice');
        $this->setDescription('InvoiceCommand');
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, 'Name'),
            new InputOption(self::NAME_OPTION, '-a', InputOption::VALUE_NONE, 'Option functionality'),
        ]);


        try {
            $this->state->setAreaCode('adminhtml');
        } catch (LocalizedException $e) {
            //            $this->state->setAreaCode('adminhtml');
        }

        parent::configure();
    }


    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {


        $incrementId = '70';

        $name   = $input->getArgument(self::NAME_ARGUMENT);

        try {

            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderRepository->get($incrementId);

            try {


                $this->erpApiInvoice->createInvoice($order);


            } catch (Exception $e) {
                dump($e->getMessage());
            }


        } catch (Exception $e) {
            dump($e->getMessage());
        }

        $output->writeln('Hello ' . $name);
    }


    public function testResponse(ResponseInterface $res){
        dump($this->erpApiClient->parseBody($res));
    }

}