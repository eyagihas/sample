<?php

namespace Modelings;

trait Payments
{
	public function create_portal_model(&$rows)
    {
        foreach($rows as $value){
            $id_array = explode(',', $value->payment_id);
            $name_array = explode(',', $value->payment_name);
            $free_ex_array = explode(',', $value->free_text_ex);

            $value->parent_id = $id_array[0];
            $value->parent_name = $name_array[0];

            $payments = [];
            unset($id_array[0]);
            unset($name_array[0]);
            unset($free_ex_array[0]);
            foreach ($id_array as $k => $v) {
                $data = new \stdClass();
                $data->payment_id = $v;
                $data->payment_name = $name_array[$k];
                $data->free_text_ex = $free_ex_array[$k];
                $payments[] = $data;
            }
        	$value->payments = $payments;
        }
    }

}
