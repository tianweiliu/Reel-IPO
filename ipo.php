<?php
  /*
  ------------------------------------------------------------------------------------------------
  Reel IPO Server v2.0
  Main IPO function page. 
  Used to communicate with CGT411/450 IPO page and Reel IPO database
  Return value is a php array
  -- By Tianwei Liu (tianwei.liu89@gmail.com)
  ------------------------------------------------------------------------------------------------
  */
    function RetrieveIPO($group_id, $semester, $key, $mode) {
		include("includes/simple_html_dom.php");
		$stock = file_get_html("http://www2.tech.purdue.edu/cgt/Courses/cgt411/COGENT/".$semester."IPO_files/sheet001.htm");
		
		$arr  = array("date" => "N/A", "name" => "N/A", "bsh" => "N/A", "writer" => "N/A", 'sold' => "N/A", "total" => "N/A", "available" => "N/A", 'value' => "N/A", "change" => "N/A", "total_value" => "N/A");
		
		$rank_count = 0;
		$last_rank_index = 0;
		$row_index = -1;
		$column_name = array();
		$group_id_index = 1;
		$found_group = false;
		$found_history_table = false;
		$history_group_id_index = 0;
		$history_start_row_index = 0;
		$last_history_index = 0;
		foreach($stock->find("tr") as $tr)
		{
			$row_index++;
			$column_index = -1;
			
			foreach($tr->find("td") as $td)
			{
				$column_index++;
				
				$data = str_replace('&nbsp;', '', $td->plaintext);
				$data = preg_replace('/[\s\h\v]/u', '', $data);
				
				
				if ($row_index == 1)
					if ($data == "rank") {
						$rank_count++;
						$last_rank_index = $column_index;
					}
				if ($row_index == 2) {
					$column_name[$column_index] = $data;
					if ($data == "group") {
						$group_id_index = $column_index;
					}
				}
				if ($row_index > 2) {
					if (strtolower($data) == "history") {
						$history_start_row_index = $row_index;
						$found_history_table = true;
						$history_group_id_index = $column_index;
						$found_group = false;
					}
					if (strtolower($data) == "avg.share") {
						$last_history_index = $column_index - 1;
					}
					if ((($column_index == $group_id_index && !$found_history_table) || ($column_index == $history_group_id_index && $found_history_table)) && strtolower($data) == strtolower($group_id))
						$found_group = true;
					if (!$found_history_table && $found_group) {
						
						//echo "Main table: ".$column_index.": ".$data."<br />";
						
						if ($column_index <= $group_id_index)
							continue;
						elseif ($column_index <= ($last_rank_index - $rank_count)) {
							if ($data != "" && $data != "$-" && $data != "#DIV/0!") {
								$i = $column_index - $group_id_index + 1;
								if ($i == 2)
									$arr["date"] = $data;
									//$arr["date"] = date("m/d/Y", $stock_time);
								elseif ($i == 3)
									$arr["name"] = $data;
								elseif ($i == 4)
									$arr["bsh"] = $data;
								elseif ($i == 5)
									$arr["writer"] = $data;
								elseif ($i == 6)
									$arr["sold"] = $data;
								elseif ($i == 7)
									$arr["total"] = $data;
								elseif ($i == 8)
									$arr["available"] = $data;
								elseif ($i == 9)
									$arr["value"] = $data;
								elseif ($i == 10)
									$arr["change"] = $data;	
								elseif ($i == 11) {
									$arr["total_value"] = $data;
								}
							}
						}
						elseif ($column_index > ($last_rank_index - $rank_count) && $column_index <= $last_rank_index) {
							$rank_index = $column_index - ($last_rank_index - $rank_count + 1);
							if ($data != "" && $data != "$-" && $data != "#DIV/0!")
								$arr["rank"][$rank_index] = $data;
						}
						elseif (($column_index == ($last_rank_index + 1)) && $mode == "manual") {
							include("dbConnect.php");
							$sql = "SELECT * FROM IPO WHERE group_id='".$group_id."' AND semester='".$semester."'";
							$result = mysql_query($sql);
							if(empty($result))
								$num_result = 0;
							else {
								$num_result = mysql_num_rows($result);
								$row = mysql_fetch_array($result);
							}
							
							if ($num_result > 0) {
								if ($key == trim($row["key"])) {
									if (trim($row["value"]) != "")
										$arr["value"] = "$".trim($row["value"]);

									if (trim($row["date"]) != "") {
										$dateReel = strtotime(trim($row["date"]));
										$dateIPO = strtotime($arr["date"]);
										if ($dateIPO < $dateReel)
											$arr["date"] = trim($row["date"]);
									}
									elseif (trim($row["uid"]) != "") {
										$sql = "SELECT date FROM stock WHERE uid='".trim($row["uid"])."'";
										$result = mysql_query($sql);
										if(empty($result))
											$num_result = 0;
										else {
											$num_result = mysql_num_rows($result);
										}
										if ($num_result > 0) {
											for($i=0; $i<$num_result; $i++) {
												$stock_row = mysql_fetch_array($result);
												$dateStock = strtotime(trim($stock_row["date"]));
												$dateIPO = strtotime($arr["date"]);
												if ($dateIPO < $dateStock)
													$arr["date"] = trim($stock_row["date"]);
											}
										}
									}									
									if (trim($row["available"]) != "")
										$arr["available"] = trim($row["available"]);
									elseif (trim($row["uid"]) != "") {
										$sql = "SELECT SUM(shares) AS sold FROM stock WHERE uid='".trim($row["uid"])."'";
										$result = mysql_query($sql);
										if(empty($result))
											$num_result = 0;
										else {
											$num_result = mysql_num_rows($result);
											$stock_row = mysql_fetch_array($result);
										}
										if ($num_result > 0) {
											$arr["available"] = intval(str_replace(",", "", $arr["total"])) - intval(trim($stock_row["sold"]));
										}
									}
								}
								mysql_close($db);
							}
						}
						else {
							$found_group = false;
							break;
						}
					}
					elseif ($found_history_table && $found_group) {
						
						//echo "History table: ".$column_index.": ".$data." ".($column_index - $history_group_id_index - 1)." ".$last_history_index."<br />";
						
						if ($column_index > $history_group_id_index && $column_index <= $last_history_index)
							if ($data != "" && $data != "$-" && $data != "#DIV/0!")
								$arr["history"][($column_index - $history_group_id_index - 1)] = str_replace("$", "", $data);
							//else
								//$arr["history"][($column_index - $history_group_id_index - 1)] = "null";
						if ($column_index > $last_history_index) {
							return $arr;
						}
					}
				}
			}
		}
	}
?>
