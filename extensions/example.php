<?php
$live->animal = "";
$live->favoriteAnimal = function($animal = null) use ($live){
	if($animal == null)
		$animal = $live->quickString;
	$live->animal = $animal;
	$live->quickString = "Your favorite animal is a $live->animal.";
	return $live;
};
$live->animalNoise = function() use ($live){
	switch($live->animal){
		case "dog":
			$live->quickString = "The {$live->animal} says Bark!";
			break;
		case "cat":
			$live->quickString = "The {$live->animal} says Meow!";
			break;
		default:
			$live->quickString = "$live->animal unknown sound.";
			break;
	}
	return $live;
};