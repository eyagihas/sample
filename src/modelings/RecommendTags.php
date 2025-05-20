<?php

namespace Modelings;

use Carbon\Carbon as Carbon;

trait RecommendTags
{
	public function create_cmslist_model(&$rows)
    {
        foreach($rows as $row){
        	$updated_at = new Carbon($row->updated_at);
			$publish_in = $updated_at->format('Y年');
			$row->publish_in = $publish_in;
        }
    }

}
