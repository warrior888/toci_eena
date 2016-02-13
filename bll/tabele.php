<?
    //include("../conf.php");
    //include("../statystyka/date_class.php");
    include("../dal.php");
    class klienci extends dal
    {
        public $klienciDef = array("nazwa");
        public $klienciName = "klienci";
        public $klienciId = "id";
        public $klienciData = array("nazwa" => "");
        public function KlienciSetData($table)
        {
            $i = 0;
            while(isset($this->klienciDef[$i]))
            {
                $this->klienciData[$this->klienciDef[$i]] = $table[$this->klienciDef[$i]];
            }
        }
        public function KlienciUpdate($id)
        {
            $query = "update $this->klienciName set ";
            $i = 0;
            while(isset($this->klienciDef[$i]))
            {
                $query .= $this->klienciDef[$i]."=".$this->klienciData[$this->klienciDef[$i]]." ";
                $i++;
            }
            $query .= "where $this->klienciId = $id;";
            $this->dbConnect();
            $this->pgQuery($query);
        }
        public function KlienciInsert()
        {
            $query = "insert into $this->klienciName values (nextval('".$this->klienciName."_id_seq'),";
            $i = 0;
            while(isset($this->klienciDef[$i]))
            {
                $query .= "'".$this->klienciData[$this->klienciDef[$i]]."',";
                $i++;
            }
            $query .= ");";
            $this->dbConnect();
            $this->pgQuery($query);
        }
        public function KlienciSelect($view)
        {
            $query = "select * from $view where";
            $i = 0;
            $and = 0;
            while(isset($this->klienciDef[$i]))
            {
                if (isset($this->klienciData[$this->klienciDef[$i]]))
                {
                    if ($and == 0)
                    {
                        $and = 1;
                    }
                    else
                    {
                        $query .= " and ";
                    }
                    $query .= $this->klienciDef[$i]."='".$this->klienciData[$this->klienciDef[$i]]."'";
                }
                $i++;
            }
            $query .= ";";
            $this->dbConnect();
            $this->pgQuery($query);
        }
    }
    class oddzialy_klient
    {
        ///???? zamysl dualistyczny : albo ustawiam w obiekcie co ma byc widac a co selectowane i generuje zapytanie, albo po prostu pobieram nazwy pol itd 
        //i po staremu kleje zapytania
        
        ///klasa dziedziczaca umozliwia select z widokow, kwesti aklauzuli where polega na tym, ze definicje w tabeli sa dziedziczone. zas 
        //wiezy iutegralnosci wymusza widok; dotyczy to tylko selectowania, bo insert i updatye leci standardowo dzieki mechanizmowi selecta z id
        
        //ha jeszcze jeden problem : pytanie czy oddzialy maja byc podefiniowane i tylko potrzebujemy tabeli posrednika do powiazania tego, czy 
        //czy oddzialy sa definiowane stricte pod klienta
    }
?>