<?php

namespace App\Services;

use App\Models\Service;

class ServiceServices {

    public function addService($label) {
        $service = new Service();
        $service->label = $label;
        $service->save();
        return $service;
    }
    public function getServiceByLabel($label) {
        return Service::where('label','=',$label)->first();
    }
    public function addMultipleServicesFromAuthors($authors) {
        $servicesIds = [];
        foreach ($authors as $author) {
                if ($author['service_id'] == '-1') {
                    if (!$service = $this->getServiceByLabel($author['customService'])) {
                        $service = $this->addService($author['customService']);
                    }
                    array_push($servicesIds,$service->service_id);
                } else {
                     array_push($servicesIds,-1);
                }
        }
        return $servicesIds;
    }

}