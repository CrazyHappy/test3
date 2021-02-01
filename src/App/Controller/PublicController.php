<?php

namespace App\Controller;
use LeadGenerator\Generator;
use LeadGenerator\Lead;
use League\Plates\Engine as Engine;
use Spatie\Async\Pool;

class PublicController extends Controller
{
    private $view = null;
    private $leadArray;
    private $counter;
    private $pool;
    public function __construct()
    {
        $vendorDir = dirname(dirname(__FILE__));
        $baseDir = dirname($vendorDir);
        $this->baseDir = $baseDir;
        $this->view =  Engine::create($baseDir . '/App/Views');
    }
    public function index() {
        var_dump($this->parsDate());
        echo $this->view->render('index', ['name' => 'Руслан']);
    }
    /**
     * @return Lead[]
     */
    public function parsDate() {

        $this->pool = Pool::create() ->concurrency(10000);

        $generator = new Generator();
        $generator->generateLeads(10000, function (Lead $lead) {
            $this->pool[] = async(function () use ($lead) {
                sleep(2);
                return 3;
            })->then(function (int $output) {
                $this->leadArray[] = $output;
            });
        });
        await($this->pool);
        d($this->leadArray);
        exit();
        return 1;
    }
    public function printTxt() {
        $items = $this->DB->select();
        $object = $this->childrens($items);
        $this->write($this->genirate($object));
        echo $this->view->render('list-menu', ['html' => $this->genirateHtml($object)]);
    }
    private function genirate($items, $url = '/', $tab = '') {
        $text = '';
        foreach ($items as $item) {
            $text = $text . $tab . $item->name . " " . $url . $item->alias . "\n";
            if (count($item->childrens) > 0)
                $text = $text . $this->genirate($item->childrens, $url . $item->alias . "/", $tab . "\t");
        }
        return $text;
    }
    private function genirateHtml($items, $url = '/', $tab = '') {
        $text = '';
        $tabHtml = "&nbsp;&nbsp;&nbsp;&nbsp;";
        foreach ($items as $item) {
            $text = $text . "<p>" . $tab . $item->name . " " . $url . $item->alias . "<p/>";
            if (count($item->childrens) > 0)
                $text = $text . $this->genirateHtml($item->childrens, $url . $item->alias . "/", $tab . $tabHtml );
        }
        return $text;
    }
    private function write($text) {
        $file = fopen( $this->baseDir . "\\type_a.txt", "w" );
        if( $file == false ) {
            echo ( "Error in opening new file" );
            exit();
        }
        fwrite( $file, $text );
        fclose( $file );
    }
    public function readFile() {
        $array = [];
        $file = new \SplFileObject($this->baseDir . '\\type_b.txt');
        $key = 0;
        $lastKey = 0;
        while (!$file->eof()) {
            $text = $file->fgets();
            if (substr_count($text, "\t") > 0) {
                $array[$lastKey]->childrens[] = (object)[
                    "name" => trim($text,  " \t\n\r\0\x0B"),
                    "alias" => $this->transliteration(trim($text,  " \t\n\r\0\x0B")),
                ];
            }
            else {
                $array[$key] = (object)[
                    "name" => trim($text,  " \t\n\r\0\x0B"),
                    "alias" => $this->transliteration(trim($text,  " \t\n\r\0\x0B")),
                ];
                $lastKey = $key;
            }
            $key++;
        }
        $this->DB->insert($array);
    }
    public function read(){

    }
    private function transliteration($text) {
        $cyr = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я',' '
        ];
        $lat = [
            'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
            'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya','-'
        ];
        return $textcyr = str_replace($cyr, $lat, $text);
    }
    private function childrens($items, $childrens = null) {
        $objects = [];
        $keys = array_keys(array_column($items, 'childrens'), $childrens);
        for ($i=0; $i < count($keys); $i++) {
            $object = (object)$items[$keys[$i]];
            $object->childrens =  $this->childrens($items, $items[$keys[$i]]['id']);
            $objects[] = $object;
        }
        return $objects;
    }
}