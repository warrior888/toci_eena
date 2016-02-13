<?
    //include("../conf.php");
    //include("../statystyka/date_class.php");
    include("../dal.php");
    class branza extends dal
    {
        public $branzaDef = array("nazwa");
        public $branzaName = "branza";
        public $branzaId = "id";
        public $branzaVis = array();
        public $branzaData = array("nazwa" => "");
        public function branzaSetData($table)
        {
            $i = 0;
            while(isset($this->branzaDef[$i]))
            {
                $this->branzaData[$this->branzaDef[$i]] = $table[$this->branzaDef[$i]];
            }
        }
        public function branzaUpdate($id)
        {
            $query = "update $this->branzaName set ";
            $i = 0;
            while(isset($this->branzaDef[$i]))
            {
                $query .= $this->branzaDef[$i]."=".$this->klienciData[$this->branzaDef[$i]]." ";
                $i++;
            }
            $query .= "where $this->branzaId = $id;";
            $this->dbConnect();
            $this->pgQuery($query);
        }
        public function branzaInsert()
        {
            $query = "insert into $this->branzaName values (nextval('".$this->branzaName."_id_seq'),";
            $i = 0;
            while(isset($this->branzaDef[$i]))
            {
                $query .= "'".$this->branzaData[$this->branzaDef[$i]]."',";
                $i++;
            }
            $query .= ");";
            $this->dbConnect();
            $this->pgQuery($query);
        }
        public function branzaSelect($view)
        {
            $query = "select * from $view where";
            $i = 0;
            $and = 0;
            while(isset($this->branzaDef[$i]))
            {
                if (isset($this->branzaData[$this->branzaDef[$i]]))
                {
                    if ($and == 0)
                    {
                        $and = 1;
                    }
                    else
                    {
                        $query .= " and ";
                    }
                    $query .= $this->branzaDef[$i]."='".$this->branzaData[$this->branzaDef[$i]]."'";
                }
                $i++;
            }
            $query .= ";";
            $this->dbConnect();
            $this->pgQuery($query);
        }
    }
    class poprzedni_pracodawca extends branza
    {
        public $popPraDef = array("id","nazwa","id_branza","id_grupa_zawodowa");
        public $popPraName = "poprzedni_pracodawca";
        public $popPraId = "id_wiersz";
        public $popPraVis = array();
        public $popPraData = array("id" => "","nazwa" => "","id_branza" => "","id_grupa_zawodowa" => "");
        public function popPraSetData($table)
        {
            $i = 0;
            while(isset($this->popPraDef[$i]))
            {
                $this->popPraData[$this->popPraDef[$i]] = $table[$this->popPraDef[$i]];
            }
        }
        public function popPraUpdate($id)
        {
            $query = "update $this->popPraName set ";
            $i = 0;
            while(isset($this->popPraDef[$i]))
            {
                $query .= $this->popPraDef[$i]."=".$this->klienciData[$this->popPraDef[$i]]." ";
                $i++;
            }
            $query .= "where $this->popPraId = $id;";
            $this->dbConnect();
            $this->pgQuery($query);
        }
        public function popPraInsert()
        {
            $query = "insert into $this->popPraName values (nextval('".$this->popPraName."_id_seq'),";
            $i = 0;
            while(isset($this->popPraDef[$i]))
            {
                $query .= "'".$this->popPraData[$this->popPraDef[$i]]."',";
                $i++;
            }
            $query .= ");";
            $this->dbConnect();
            $this->pgQuery($query);
        }
        public function popPraSelect($view)
        {
            $query = "select "; 
            $i = 0;
            $przecinek = 0;
            while (isset($this->branzaVis[$i]))
            {
                if ($przecinek == 0)
                {
                    $przecinek = 1;
                }
                else
                {
                    $przecinek .= ",";
                }
                $query .= $this->branzaName.".".$this->branzaDef[$this->branzaVis[$i]];
                $i++;
            }
            $i = 0;
            while (isset($this->popPraVis[$i]))
            {
                if ($przecinek == 0)
                {
                    $przecinek = 1;
                }
                else
                {
                    $przecinek .= ",";
                }
                $query .= $this->popPraName.".".$this->popPraDef[$this->popPraVis[$i]];
                $i++;
            }
            $query .= " from $view where";
            $i = 0;
            $and = 0;
            while(isset($this->popPraDef[$i]))
            {
                if (isset($this->popPraData[$this->popPraDef[$i]]))
                {
                    if ($and == 0)
                    {
                        $and = 1;
                    }
                    else
                    {
                        $query .= " and ";
                    }
                    $query .= $this->popPraName.".".$this->popPraDef[$i]."='".$this->popPraData[$this->popPraDef[$i]]."'";
                }
                $i++;
            }
            while(isset($this->branzaData[$i]))
            {
                if (isset($this->branzaData[$this->branzaDef[$i]]))
                {
                    if ($and == 0)
                    {
                        $and = 1;
                    }
                    else
                    {
                        $query .= " and ";
                    }
                    $query .= $this->branzaName.".".$this->branzaDef[$i]."='".$this->branzaData[$this->branzaDef[$i]]."'";
                }
                $i++;
            }
            $query .= ";";
            $this->dbConnect();
            $this->pgQuery($query);
        }
    }
    
    /*
    Hmmmm !!!!!!!!
    
    Pomysl jest nastepujacy: nalezy zdefiniowac statyczna metode, jej zadaniem jest zwrocenie nazwy funkcji z valelclass dodajacej element 
    na ekran w zaleznosci od typu i pewnie walidacje - chodzi o dodawanie elementow z vaelclass; zasada po to taka, zeby formularz konstruowal 
    sie na bazie defionicji tabeli w bazie
    
    inna sprawa co do samej definicji klasy: dorobic propercje umozliwiajace ustawianie wszystkich parametrow lacznie z typem danej w tabeli, 
    klase powyzsza zgeneralizowac tak, zeby dla kazdych tabel dzialalo, tylko same definicje tabel beda gdzie indziej
    
    po podaniu definicji tabeli na bazie do obiektu mozna ustawiac nastepne oczekiwania wzgledem obiektu i odpalac pytania
    
    najlepiej z jednej klasy zwracac definicje tabel
    
    docelowo na bazie klasy ma sie budowac formatka inserta, update, do update potrzeba selecta, delete to banal - bez komentarza
    
    WAZNE : zagniezdzic do 4 klasy dziedzicacej, nastepnie uzupelniac klase definicja tabel i wykonywac wszystkie operacje: najciezsze sa selecty:
    powinien powstac widok sklejajacy wszystko do kupy, nastepnie z niego beda pobierane wg definicji tych pol visible 
    pole tabeli data bedzie mialo warunki selecta do klauzuli where.
    */
?>