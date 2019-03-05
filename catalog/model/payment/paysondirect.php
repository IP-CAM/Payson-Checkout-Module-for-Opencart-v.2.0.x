<?php

class ModelPaymentPaysondirect extends Model {

    private $currency_supported_by_p_direct = array('SEK', 'EUR');
    private $minimumAmount = 4;

    private function getPaymentMethods($paymentMethod) {
        $opts = array(
            0 => array(''),
            1 => array('card'),
            2 => array('bank'),
            3 => array('invoice'),
            4 => array('bank', 'card'),
            5 => array('bank', 'invoice'),
            6 => array('card', 'invoice'),
            7 => array('bank', 'card', 'invoice'),
        );
        return $opts[$paymentMethod];
    }

    public function getMethod($address, $total) {
        $this->language->load('payment/paysondirect');

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('paysondirect_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        if ($this->config->get('paysondirect_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('paysondirect_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }
        if (!in_array(strtoupper($this->session->data['currency']), $this->currency_supported_by_p_direct)) {
            $status = false;
        }
        if (strtoupper($this->config->get('config_currency')) == 'SEK' && $total < $this->minimumAmount) {
            $status = false;
        }
        if (strtoupper($this->config->get('config_currency')) != 'SEK' && $this->currency->convert($total, strtoupper($this->config->get('config_currency')), 'SEK') < $this->minimumAmount) {
            $status = false;
        }
        $method_data = array();
        if ($status) {

            $constraints = $this->getPaymentMethods($this->config->get('paysondirect_payment_method'));

            if (in_array('invoice', $constraints)) {
                $title = $this->language->get('text_AION_INVOICE');
            }
            if (!in_array('invoice', $constraints)) {
                $title = $this->language->get('text_title');
            }

            $method_data = array(
                'code' => 'paysondirect',
                'title' => 'Payson All in One<br />',
                'image' => "catalog/view/theme/default/image/p_payment_payson.png",
                'terms' => $title,
                'sort_order' => $this->config->get('paysondirect_sort_order')
            );
        }

        return $method_data;
    }

}

?>