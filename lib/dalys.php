<?php 
if(!isset($_SESSION)) 
{ 
    session_start(); 
}
class dalys{

	public $dalys_lentele = '';
	public $daliu_tipai_lentele = '';

	private $klientai_lentele = '';
	private $vartotojai_lentele = '';
	private $miestu_lentele = '';

	private $mokejimu_lentele = '';
	private $sutarciu_lentele = '';

	public function __construct(){
		$this->dalys_lentele = 'Dalys';
		$this->daliu_tipai_lentele = 'dalies_tipai';
		$this->klientai_lentele = 'Klientai';
		$this->vartotojai_lentele = 'vartotojai';
		$this->miestu_lentele = 'Miestai';
		$this->mokejimu_lentele= 'Mokejimai';
		$this->sutarciu_lentele = 'Sutartys';
	}

	public function irasyti_pirkimus(){
		$pay = '';
		$state = '';
		if($_SESSION['payment'] == "Bank transfer"){
			$pay = 0;
			$state = 2;
		}
		if($_SESSION['payment'] == "In the shop"){
			$pay = 1;
			$state = 0;
		}

		$queryPayment = "INSERT INTO {$this->mokejimu_lentele}
				(
					data,
					suma,
					fk_Mokejimo_tipas,
					fk_Mokejimo_busena
				)
				VALUES
				(
					 NOW(),
					'{$_SESSION['totalPrice']}',
					'{$pay}',
					'{$state}'
				)";
		mysql::query($queryPayment);

		$query = "SELECT id
					FROM {$this->klientai_lentele} WHERE fk_Vartotojas='{$_SESSION['userid']}'";
		$data = mysql::select($query);

		$queryContract = "INSERT INTO {$this->sutarciu_lentele}
				(
					sutarties_data,
					kaina,
					pristatymas,
					sutarties_busena,
					fk_Klientas,
					fk_Remontas,
					fk_Mokejimas,
					fk_Darbuotojas
				)
				VALUES
				(
					 NOW(),
					'{$_SESSION['totalPrice']}',
					'{$_SESSION['deliveryOption']}',
					'1',
					'{$data[0]['id']}',
					'0',
					'1',
					'1'
				)";

		mysql::query($queryContract);
	}

	public function gauti_kliento_duomenis(){

		$query = "SELECT *
					FROM {$this->klientai_lentele} WHERE fk_Vartotojas='{$_SESSION['userid']}'";
		$data = mysql::select($query);
		return $data;
	}

	public function gauti_miesta($cityId){
		$query = "SELECT pavadinimas
					FROM {$this->miestu_lentele} WHERE miesto_kodas='{$cityId}'";
		$data = mysql::select($query);
		return $data;
	}

	public function pasirinkti_filtrus($filter, $selectedBrands){
		$limitOffsetString = "";
		if(isset($limit)) {
			$limitOffsetString .= " LIMIT {$limit}";
			
			if(isset($offset)) {
				$limitOffsetString .= " OFFSET {$offset}";
			}	
		}
		$data;
		if($selectedBrands != 'false'){
			$brandsText = '';
			foreach ($selectedBrands as $brand) {
				$brandsText = $brandsText . "'$brand',";
			}
			$query = "SELECT *
			FROM {$this->dalys_lentele}{$limitOffsetString}
			WHERE dalies_tipas='{$filter}' AND gamintojas IN ({$brandsText}";
			$query = substr($query, 0, -1);
			$query = $query . ");";
			$data = mysql::select($query);
		}
		else{
			$query = "SELECT *
					FROM {$this->dalys_lentele}{$limitOffsetString}
					WHERE dalies_tipas='{$filter}'";
			$data = mysql::select($query);
		}
		
		return $data;
	}

	public function gamintojai($filter){
		$query = "SELECT DISTINCT gamintojas
					FROM {$this->dalys_lentele} WHERE dalies_tipas='{$filter}'";
		$data = mysql::select($query);
		return $data;
	}

	public function filtruotu_irasu_kiekis($filter){
		$query = "SELECT COUNT(`id`) as `kiekis`
					FROM {$this->dalys_lentele}
					WHERE dalies_tipas = '{$filter}'";
		$data = mysql::select($query);
		
		return $data[0]['kiekis'];
	}


	public function perziureti_dalis($limit = null, $offset = null){
		$limitOffsetString = "";
		if(isset($limit)) {
			$limitOffsetString .= " LIMIT {$limit}";
			
			if(isset($offset)) {
				$limitOffsetString .= " OFFSET {$offset}";
			}	
		}
		
		$query = "SELECT *
					FROM {$this->dalys_lentele}{$limitOffsetString}";
		$data = mysql::select($query);
		
		return $data;
	}

	public function irasu_kiekis(){
		$query = "SELECT COUNT(`id`) as `kiekis`
					FROM {$this->dalys_lentele}";
		$data = mysql::select($query);
		
		return $data[0]['kiekis'];
	}

	public function gauti_dali($id){
		$query = "SELECT * 
					FROM {$this->dalys_lentele}
					LEFT JOIN {$this->daliu_tipai_lentele}
					ON {$this->dalys_lentele}.dalies_tipas = {$this->daliu_tipai_lentele}.id_dalies_tipas
					WHERE id = '{$id}' ";
		$data = mysql::select($query);
		return $data;
	}


	public function gautiTipus($limit = null, $offset = null){
		$limitOffsetString = "";
		if(isset($limit)) {
			$limitOffsetString .= " LIMIT {$limit}";
			
			if(isset($offset)) {
				$limitOffsetString .= " OFFSET {$offset}";
			}	
		}
		
		$query = "SELECT *
					FROM {$this->daliu_tipai_lentele}{$limitOffsetString}";
		$data = mysql::select($query);
		
		return $data;
	}

	public function pakeisti_duomenis($data, $id){
		$query = "UPDATE {$this->dalys_lentele}
					SET gamintojas='{$data['brand']}',
						aprasymas='{$data['description']}',
						svoris='{$data['weigth']}',
						pagaminimo_data='{$data['date']}',
						kiekis='{$data['amount']}',
						garantijos_laikotarpis='{$data['warranty']}',
						pristatymo_laikas='{$data['delivery']}',
						kaina='{$data['price']}',
						dalies_tipas='{$data['type']}'
						WHERE id='{$id}';
		";
		mysql::query($query);
	}

	public function irasyti($data){
		$query = "INSERT INTO {$this->dalys_lentele}
				(
					gamintojas,
					aprasymas,
					svoris,
					pagaminimo_data,
					kiekis,
					garantijos_laikotarpis,
					pristatymo_laikas,
					kaina,
					dalies_tipas
				)
				VALUES
				(
					'{$data['brand']}',
					'{$data['description']}',
					'{$data['weigth']}',
					'{$data['date']}',
					'{$data['amount']}',
					'{$data['warranty']}',
					'{$data['delivery']}',
					'{$data['price']}',
					'{$data['type']}'
				)";
		mysql::query($query);
	}

	public function trinti_dali($id){
		$query = "DELETE FROM {$this->dalys_lentele}
					WHERE `id`='{$id}'";
		mysql::query($query);
	}


}

?>