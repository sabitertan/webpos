<?php
class ModelTotalWebposTotal extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
		$this->load->language('total/webpostotal');
		$webpos_title='';
		$webpos_total=0;
		if (isset($this->session->data['instalment'])){
		$bank_array=explode('_',$this->session->data['instalment']);
		$instalment_array=explode('x',$bank_array[1]);
		$instalment=intval($instalment_array[0]);
		$instalment_array[1]= str_replace(',','',$instalment_array[1]);
		$price=floatval(substr($instalment_array[1],0,-2));
		$ratio=floatval($this->config->get('webpostotal_single_ratio'));
		if($instalment!=0){
		$webpos_total=$price*$instalment;
		$webpos_title=$this->language->get('text_total').'('.$bank_array[1].')';
		} else {
			if ($ratio>=0){
				$webpos_title=$this->language->get('text_single_positive').'(%'.$ratio.')';
			} else if($ratio<0){
				$webpos_title=$this->language->get('text_single_negative').'(%'.$ratio.')';
			}
			//$webpos_total=$price+($price*$ratio/100);
			$webpos_total=$price;
		}
	}
		$total+=$webpos_total-$total;
		$total_data[] = array(
			'code'       => 'webpostotal',
			'title'      => $webpos_title,
			'value'      => $webpos_total,
			'sort_order' => $this->config->get('webpostotal_sort_order')
		);
	}
}