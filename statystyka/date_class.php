<?php
	class DateConverter
	{
		private static $dateMatrix;
		private static $dateFrom;
		private static $dateUntil;
		private static $Pointer = 0;
		
		public static function ConvertDate($date, $mode)
		{
            DateConverter::$dateMatrix = null;
			DateConverter::CreateRange($date[0], DateConverter::CalculateCount($date, $mode), $mode);
		}

		public static function MoveNext()
		{
			$tmp = array();
			
			if (DateConverter::$Pointer < count(DateConverter::$dateMatrix))
			{
				$tmp = DateConverter::$dateMatrix[DateConverter::$Pointer];
				DateConverter::$dateFrom = $tmp["dateFrom"];
				DateConverter::$dateUntil = $tmp["dateUntil"];
				DateConverter::$Pointer++;
				return true;
			}
			else
			{
				return false;
			}
		}
		
		public static function GetDateFrom()
		{
			return DateConverter::$dateFrom;
		}
		
		public static function GetDateUntil()
		{
			return DateConverter::$dateUntil;
		}
		
		public static function GetDateMatrix()
		{
			return DateConverter::$dateMatrix;
		}
		
		public static function Reset()
		{
			DateConverter::$Pointer = 0;
		}
        
        public static function AddOneDayToDateUntil()
        {
            foreach (DateConverter::$dateMatrix as $key => $value)
            {
                $date = DateConverter::ExplodeDate($value['dateUntil']);
                $time = mktime(0, 0, 0, $date['miesiac'], $date['dzien'] + 1, $date['rok']);
                $value['dateUntil'] = date("Y-m-d", $time);
                DateConverter::$dateMatrix[$key] = $value;
            }                                             
        }
		
		private static function CreateRange($firstDate, $count, $mode)
		{
			DateConverter::Reset();
			
			$date = DateConverter::ExplodeDate($firstDate);
			
			switch ($mode)
			{
				case "dzien":
					{
						DateConverter::FillRangeMatrix($date, 0, 86400, $count);
						break;	
					}
				case "tydzien":
					{
						DateConverter::FillRangeMatrix($date, 518400, 604800, $count);
						break;
					}
				case "miesiac":
					{
						DateConverter::FillRangeMatrixForMonths($date, $count);
						break;
					}
				case "rok":
					{
						DateConverter::FillRangeMatrixForYears($date, $count);
						break;
					}
				case "poniedzialek_piatek":
					{
						DateConverter::FillRangeMatrixForMondaysAndFridays($date, $count);
                        break;
					}
				case "czwartek":
					{
						DateConverter::FillRangeMatrixForThursday($date, $count);
                        break;
					}
				
			}
		}
		
		private static function ExplodeDate($date)
		{
			$result = array();
			$tmp = array();
			
			$tmp = explode("-", $date);
			$result["dzien"] = $tmp[2];
			$result["miesiac"] = $tmp[1];
			$result["rok"] = $tmp[0];			
			
			return $result;
		}
		
		private static function FillRangeMatrix($date, $firstSum, $secondSum, $count)
		{
			$tmpMatrix = array();
			$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"], $date["rok"]);
			
			$tmpMatrix["dateFrom"] = date("Y-m-d", $time);
			$tmpMatrix["dateUntil"] = date("Y-m-d", $time + $firstSum);
			DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
			DateConverter::$Pointer++; 
						
			for ($i = 0; $i < $count - 1; $i++)
			{
				$tmpTime = $time + $secondSum * ($i + 1);
				$tmpMatrix["dateFrom"] = date("Y-m-d", $tmpTime);
				$tmpMatrix["dateUntil"] = date("Y-m-d", $tmpTime + $firstSum);
				DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
				DateConverter::$Pointer++;								
			}
		}
		
		private static function FillRangeMatrixForMondaysAndFridays($date, $count)
		{
			$tmpMatrix = array();
			$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"], $date["rok"]);
			
			$day = date("w", $time);
			
			switch ($day)
			{
				case "0":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] + 1, $date["rok"]);
						break;
					}
				case "2":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] - 1, $date["rok"]);
						break;
					}
				case "3":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] - 2, $date["rok"]);
						break;
					}
				case "4":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] - 3, $date["rok"]);
						break;
					}
				case "5":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] - 4, $date["rok"]);
						break;
					}
				case "6":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] - 5, $date["rok"]);
						break;
					}
			}
			
			$mondayTime = $time;
			$fridayTime = $time + 345600;
			
			$monday = date("Y-m-d", $mondayTime);
			$friday = date("Y-m-d", $fridayTime);
			
			$tmpMatrix["dateFrom"] = $monday;
			$tmpMatrix["dateUntil"] = $monday;
			
			DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
			DateConverter::$Pointer++;

			$tmpMatrix["dateFrom"] = $friday;
			$tmpMatrix["dateUntil"] = $friday;
			
			DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
			DateConverter::$Pointer++;
						
			for ($i = 0; $i < $count - 1; $i++)
			{
				$mondayTime = $mondayTime + 604800;
				$fridayTime = $fridayTime + 604800;
				$tmpMatrix["dateFrom"] = date("Y-m-d", $mondayTime);
				$tmpMatrix["dateUntil"] = date("Y-m-d", $mondayTime);
				DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
				DateConverter::$Pointer++;

				$tmpMatrix["dateFrom"] = date("Y-m-d", $fridayTime);
				$tmpMatrix["dateUntil"] = date("Y-m-d", $fridayTime);
				DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
				DateConverter::$Pointer++;
			}
		}
		
		private static function FillRangeMatrixForThursday($date, $count)
		{
			$tmpMatrix = array();
			$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"], $date["rok"]);
			
			$day = date("w", $time);
			
			switch ($day)
			{
				case "0":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] + 4, $date["rok"]);
						break;
					}
				case "1":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] + 3, $date["rok"]);
						break;
					}
				case "2":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] + 2, $date["rok"]);
						break;
					}
				case "3":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] + 1, $date["rok"]);
						break;
					}
				case "5":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] - 1, $date["rok"]);
						break;
					}
				case "6":
					{
						$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"] - 2, $date["rok"]);
						break;
					}
			}
			
			$tmpMatrix["dateFrom"] = date("Y-m-d", $time);
			$tmpMatrix["dateUntil"] = date("Y-m-d", $time);
			DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
			DateConverter::$Pointer++; 
						
			for ($i = 0; $i < $count - 1; $i++)
			{
				$tmpTime = $time + 604800 * ($i + 1);
				$tmpMatrix["dateFrom"] = date("Y-m-d", $tmpTime);
				$tmpMatrix["dateUntil"] = date("Y-m-d", $tmpTime);
				DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
				DateConverter::$Pointer++;								
			}
			
		}	
		
		private static function FillRangeMatrixForMonths($date, $count)
		{
			$tmpMatrix = array();
			$time = mktime(0, 0, 0, $date["miesiac"], $date["dzien"], $date["rok"]);
			
			$tmpMatrix["dateFrom"] = date("Y-m-d", $time);
			$tmpMatrix["dateUntil"] = date("Y-m-d", mktime(0, 0, 0, $date["miesiac"] + 1, $date["dzien"] - 1, $date["rok"]));
			DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
			DateConverter::$Pointer++; 
						
			for ($i = 0; $i < $count - 1; $i++)
			{
				$tmpMatrix["dateFrom"] = date("Y-m-d", mktime(0, 0, 0, $date["miesiac"] + ($i + 1), $date["dzien"], $date["rok"]));
				$tmpMatrix["dateUntil"] = date("Y-m-d", mktime(0, 0, 0, $date["miesiac"] + ($i + 2), $date["dzien"] - 1, $date["rok"]));
				DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
				DateConverter::$Pointer++;								
			}
		}
		
		private static function FillRangeMatrixForYears($date, $count)
		{
			$tmpMatrix = array();
			
			$tmpMatrix["dateFrom"] = date("Y-m-d",mktime(0, 0, 0, $date["miesiac"], $date["dzien"], $date["rok"]));
			$tmpMatrix["dateUntil"] = date("Y-m-d", mktime(0, 0, 0, $date["miesiac"], $date["dzien"] - 1, $date["rok"] + 1));
			DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
			DateConverter::$Pointer++; 
						
			for ($i = 0; $i < $count - 1; $i++)
			{
				$tmpMatrix["dateFrom"] = date("Y-m-d", mktime(0, 0, 0, $date["miesiac"], $date["dzien"], $date["rok"] + ($i + 1)));
				$tmpMatrix["dateUntil"] = date("Y-m-d", mktime(0, 0, 0, $date["miesiac"], $date["dzien"] - 1, $date["rok"] + ($i + 2)));
				DateConverter::$dateMatrix[DateConverter::$Pointer] = $tmpMatrix;
				DateConverter::$Pointer++;								
			}	
		}
		
		private static function CalculateCount($date, $mode)
		{
			$result = 0;
			
			switch ($mode)
			{
				case "dzien":
					{
						$result = DateConverter::CalculateCountForDaysAndWeeks($date, 1);
						break;
					}
				case "tydzien":
					{
						$result = DateConverter::CalculateCountForDaysAndWeeks($date, 7);
						break;
					}
				case "miesiac":
					{
						$result = DateConverter::CalculateCountForMoths($date);
						break;
					}
				case "rok":
					{
						$result = DateConverter::CalculateCountForYears($date);
						break;
					}
				case "poniedzialek_piatek":
					{
						$result = DateConverter::CalculateCountForDaysAndWeeks($date, 7);
						break;
					}
				case "czwartek":
					{
						$result = DateConverter::CalculateCountForDaysAndWeeks($date, 7);
						break;
					}
			}
			
			return $result;
		}
		
		private static function CalculateCountForDaysAndWeeks($date, $div)
		{
			$result = 0;
			$dateFrom = DateConverter::ExplodeDate($date[0]);
			$dateUntil = DateConverter::ExplodeDate($date[1]);
			if ($dateFrom["rok"] == $dateUntil["rok"])
			{
				$timeFrom = mktime(0, 0, 0, $dateFrom["miesiac"], $dateFrom["dzien"], $dateFrom["rok"]);
				$timeUntil = mktime(0, 0, 0, $dateUntil["miesiac"], $dateUntil["dzien"], $dateUntil["rok"]);
							
				// + 1 zeby domknac zakres
				$result = (int)date("z", $timeUntil) - (int)date("z", $timeFrom) + 1;
			}
			else
			{
				$countYears = $dateUntil["rok"] - $dateFrom["rok"];
				$countDays = 0;
							
				for ($i = 0; $i < $countYears - 1; $i++)
				{
					$timeTmp = mktime(0, 0, 0, 1, 1, $dateFrom["rok"] + $i);
					if ((int)date("L", $timeTmp) == 1)
					{
						//rok przestepny
						$countDays += 366;
					}
					else
					{
						//zwykly rok
						$countDays += 365;
					}
				}
				$timeFrom = mktime(0, 0, 0, $dateFrom["miesiac"], $dateFrom["dzien"], $dateFrom["rok"]);
				$timeUntil = mktime(0, 0, 0, $dateUntil["miesiac"], $dateUntil["dzien"], $dateUntil["rok"]);
				$yearFrom = mktime(0, 0, 0, 12, 31, $dateFrom["rok"]);
							
				// + 2 zeby domknac zakres
				$result = ((int)date("z", $yearFrom) - (int)date("z", $timeFrom)) + (int)date("z", $timeUntil) + $countDays + 2;
			}
			
			return (int)($result / $div);
		}
		
		private static function CalculateCountForMoths($date)
		{
			$result = 0;
			$dateFrom = DateConverter::ExplodeDate($date[0]);
			$dateUntil = DateConverter::ExplodeDate($date[1]);
			if ($dateFrom["rok"] == $dateUntil["rok"])
			{
				$result = $dateUntil["miesiac"] - $dateFrom["miesiac"];
			}
			else
			{
				$countYears = $dateUntil["rok"] - $dateFrom["rok"];
				$result = (12 - $dateFrom["miesiac"] + $dateUntil["miesiac"]) + (($countYears - 1) * 12);
			}
			
			return (int)$result;
		}

		private static function CalculateCountForYears($date)
		{
			$result = 0;
			$dateFrom = DateConverter::ExplodeDate($date[0]);
			$dateUntil = DateConverter::ExplodeDate($date[1]);
			
			$result = $dateUntil["rok"] - $dateFrom["rok"];
			
			return (int)$result;
		}
	}
	
	//mode: dzien, tydzien, miesiac, rok, poniedzialek_piatek
	//pierwszym parametrem ConvertDate musi byc dwuelementowa tablica - nigdzie nie jest to weryfikowane
	//przyklad uzycia
?>