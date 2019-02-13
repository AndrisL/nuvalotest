<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller {

    private $storagePath;
    private $datePickerFormat = 'd-m-Y';
    private $minDate = '1901-02-31';
    private $maxDate = '2018-04-22';

    function __construct() {
        $this->storagePath = storage_path() . DIRECTORY_SEPARATOR . 'requetCache' . DIRECTORY_SEPARATOR;
    }

    public function index() {
        $cacheEmployee = $this->storagePath . 'employee';

        $employeeData = @file_get_contents($cacheEmployee);
        // $employeeData = false;
        if (!$employeeData) {
            $jsonAnswer = $this->restRequest($this->minDate, $this->maxDate);
            if (count($jsonAnswer)) {
                foreach ($jsonAnswer AS $id => $data) {
                    $employeeData[$data['employee']['id']] = $data['employee']['fname'] . ' ' . $data['employee']['lname'];
                }

                file_put_contents($cacheEmployee, serialize($employeeData));
            }
        } else {
            $employeeData = unserialize($employeeData);
        }

        if (!$employeeData) {
            $employeeData = [];
        }

        $maxDate = \DateTime::createFromFormat('Y-m-d', $this->maxDate);
        return view('index', [
            'employeeData' => $employeeData,
            'maxDate' => $maxDate->format($this->datePickerFormat)
        ]);
    }

    function getHours(Request $request) {
        $dateFormat = 'd-m-Y';
        $this->validate($request, [
            'worker' => 'required|integer',
            'datestart' => 'required|date_format:"' . $dateFormat . '"',
            'dateend' => 'required|date_format:"' . $dateFormat . '"',
        ]);

        $dateStart = \DateTime::createFromFormat($dateFormat, $request->datestart);
        $dateEnd = \DateTime::createFromFormat($dateFormat, $request->dateend);
        $startDate = $dateStart->format('Y-m-d');
        $endDate = $dateEnd->format('Y-m-d');

        $start = strtotime($startDate);
        $end = strtotime($endDate);

        $minDate = strtotime($this->minDate);
        $maxDate = strtotime($this->maxDate);

        if ($start < $minDate || $start > $maxDate) {
            $startDate = $this->minDate;
        }

        if ($end < $minDate || $end > $maxDate) {
            $endDate = $this->maxDate;
        }


        $cacheFile = $this->storagePath . 'hour' . $request->worker . $startDate . $endDate;
        $cacheData = @file_get_contents($cacheFile);
        // $cacheData = false;
        if (!$cacheData) {
            $jsonAnswer = $this->restRequest($startDate, $endDate);
            if (count($jsonAnswer)) {
                $cacheData = [];
                foreach ($jsonAnswer AS $id => $data) {
                    if ($request->worker == $data['employee_id']) { // 2018-04-01 12:16:16
                        $difirent = strtotime($data['end']) - strtotime($data['start']);
                        $key = date('Y-m', strtotime($data['start']));
                        if (array_key_exists($key, $cacheData)) {
                            $cacheData[$key]['secondAll'] += $difirent;
                        } else {
                            $cacheData[$key]['secondAll'] = $difirent;
                        }
                    }
                }

                if ($cacheData) {
                    foreach ($cacheData AS $id => $cd) {
                        $minutes = floor($cd['secondAll'] / 60);
                        $hours = floor($minutes / 60);

                        $cacheData[$id]['hours'] = $hours;
                        $cacheData[$id]['minutes'] = $minutes - ($hours * 60);
                    }
                }

                file_put_contents($cacheFile, serialize($cacheData));
            }
        } else {
            $cacheData = unserialize($cacheData);
        }

        if (!$cacheData) {
            $cacheData = [];
        }

        return $cacheData;
    }

    private function restRequest($start, $end) {
        $queryParam = http_build_query(['start' => $start, 'end' => $end]);
        try {
            $res = @file_get_contents('https://nuvalo.merrant.ee/workhours?' . $queryParam);
        } catch (Exception $exc) {
            return [];
        }

        if ($res) {
            return json_decode($res, true);
        }

        return [];
    }

}
