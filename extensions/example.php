<?php
$live->animal = "";
$live->favoriteAnimal = function($animal = null) use ($live){
	if($animal == null)
		$animal = $live->string;
	$live->animal = $animal;
	$live->string = "Your favorite animal is a $live->animal.";
	return $live;
};
$live->animalNoise = function() use ($live){
	switch($live->animal){
		case "dog":
			$live->string = "The {$live->animal} says Bark!";
			break;
		case "cat":
			$live->string = "The {$live->animal} says Meow!";
			break;
		default:
			$live->string = "$live->animal unknown sound.";
			break;
	}
	return $live;
};