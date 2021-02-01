<?php

namespace App\Controller;
use Carbon\Carbon;
use LeadGenerator\Generator;
use LeadGenerator\Lead;
use League\Plates\Engine as Engine;
use Spatie\Async\Pool;

class PublicController extends Controller
{
    private $view = null;
    private $leadArray;
    private $counter = ' ';
    private $pool;
    public function __construct()
    {
        $vendorDir = dirname(dirname(__FILE__));
        $baseDir = dirname($vendorDir);
        $this->baseDir = $baseDir;
        $this->view =  Engine::create($baseDir . '/App/Views');
    }
    public function index() {
        echo $this->view->render('index', [
            'name' => 'Руслана',
        ]);
    }
    public function render() {
        $start = microtime(true);
        $this->parsDate();
        echo $this->view->render('render', [
            'status' => $this->parsDate(),
            'timer' => $time = microtime(true) - $start
        ]);
    }
    public function readTxt() {
        echo $this->view->render('read-txt');
    }
    private function parsDate():int {
        $this->pool = Pool::create() ->concurrency(10000);
        $generator = new Generator();
        $generator->generateLeads(50, function (Lead $lead) {
            $this->pool[] = async(function () use ($lead) {
                sleep(2);
                $renderFail = $lead;
                return $renderFail;
            })->then(function (Lead $output) {
                $this->leadArray[] = $output;
                $this->counter .= $this->setValue($output);
            });
        });
        await($this->pool);
        $this->write($this->counter);
        return 1;
    }
    private function write($text):void {
        $file = fopen( $this->baseDir . "/public/log.txt", "w" );
        if( $file == false ) {
            echo ( "Error in opening new file" );
            exit();
        }
        fwrite( $file, $text );
        fclose( $file );
    }
    private function setValue(Lead $lead) {
        $mutable = Carbon::now();
        $string = '';
        $string .= isset($lead->id) ? $lead->id . ' | ' : '';
        $string .= isset($lead->categoryName) ? $lead->categoryName . ' | ' : '';
        $string .= $mutable->format('Y-m-d H:i:s.u') . "\n ";
        return $string;
    }

}