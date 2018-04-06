<?php

namespace App\Services;


use DateTime;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Utils
{

    public static function convertDate($date, $delimter)
    {
        $arr = explode($delimter, $date);
        $year = $arr[2];
        $time = '';
        if (strlen($year) > 4) {
            $time = substr($year, 4);
            $year = substr($year, 0, 4);
        }
        return $year . '-' . $arr[1] . '-' . $arr[0] . $time;
    }

    public static function calculateWeek($start_date, $end_date)
    {

        $start_date = DateTime::createFromFormat('Y-m-d', $start_date);
        $end_date = DateTime::createFromFormat('Y-m-d', $end_date);

        return ceil($start_date->diff($end_date)->days / 7);
    }

    public static function datediff($interval, $datefrom, $dateto, $using_timestamps = false)
    {
        /*
        $interval can be:
        yyyy - Number of full years
        q    - Number of full quarters
        m    - Number of full months
        y    - Difference between day numbers
               (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
        d    - Number of full days
        w    - Number of full weekdays
        ww   - Number of full weeks
        h    - Number of full hours
        n    - Number of full minutes
        s    - Number of full seconds (default)
        */

        if (!$using_timestamps) {
            $datefrom = strtotime($datefrom, 0);
            $dateto = strtotime($dateto, 0);
        }

        $difference = $dateto - $datefrom; // Difference in seconds
        $months_difference = 0;

        switch ($interval) {
            case 'yyyy': // Number of full years
                $years_difference = floor($difference / 31536000);
                if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom) + $years_difference) > $dateto) {
                    $years_difference--;
                }

                if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto) - ($years_difference + 1)) > $datefrom) {
                    $years_difference++;
                }

                $datediff = $years_difference;
                break;

            case "q": // Number of full quarters
                $quarters_difference = floor($difference / 8035200);

                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom) + ($quarters_difference * 3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;
                }

                $quarters_difference--;
                $datediff = $quarters_difference;
                break;

            case "m": // Number of full months
                $months_difference = floor($difference / 2678400);

                while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom) + ($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
                    $months_difference++;
                }

                $months_difference--;

                $datediff = $months_difference;
                break;

            case 'y': // Difference between day numbers
                $datediff = date("z", $dateto) - date("z", $datefrom);
                break;

            case "d": // Number of full days
                $datediff = floor($difference / 86400);
                break;

            case "w": // Number of full weekdays
                $days_difference = floor($difference / 86400);
                $weeks_difference = floor($days_difference / 7); // Complete weeks
                $first_day = date("w", $datefrom);
                $days_remainder = floor($days_difference % 7);
                $odd_days = $first_day + $days_remainder; // Do we have a Saturday or Sunday in the remainder?

                if ($odd_days > 7) { // Sunday
                    $days_remainder--;
                }

                if ($odd_days > 6) { // Saturday
                    $days_remainder--;
                }

                $datediff = ($weeks_difference * 5) + $days_remainder;
                break;

            case "ww": // Number of full weeks
                $datediff = floor($difference / 604800);
                break;

            case "h": // Number of full hours
                $datediff = floor($difference / 3600);
                break;

            case "n": // Number of full minutes
                $datediff = floor($difference / 60);
                break;

            default: // Number of full seconds (default)
                $datediff = $difference;
                break;
        }

        return $datediff;
    }

    public
    static function getDateAddingWeek($date, $pas)
    {

        $date = DateTime::createFromFormat('Y-m-d', $date);
        $additional_week = \DateInterval::createFromDateString($pas . ' week');

        $date->add($additional_week);

        return $date->format('Y-m-d');

    }

    function base64_to_jpeg($base64_string, $output_file)
    {
        $ifp = fopen($output_file, "wb");
        // $data = explode(',', $base64_string);
        fwrite($ifp, base64_decode($base64_string));
        fclose($ifp);

        return $output_file;
    }

    public
    static function generateQRcode($QRcode, $qrcodeName = null)
    {

        QrCode::format("png")
            ->size(200)
            ->generate($QRcode, public_path() . "/" . $qrcodeName);
    }

    public
    static function generateCode($id, $length = 6)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString . $id;
    }

}