<?php
require_once '../../phpLive.php';
echo <<<FORM
<form method="post">
	Which animal do you prefer?<br />
	<input type="radio" name="animal" value="dog" /> Dog<br />
	<input type="radio" name="animal" value="cat" /> Cat<br />
	<input type="submit" value="What Sound?" />
</form>
FORM;

/**
 * phpLive::favoriteAnimal()
 * phpLive::animalNoise()
 *
 * The two methods can be found in extensions/example.php
 */

if($live->post()){
	echo <<<ANIMAL
		{$live->post("animal")->favoriteAnimal()}
		{$live->animalNoise()}
ANIMAL;
}

echo "<hr />";
echo $live->highlight("extend.php", HIGHLIGHT_PHP, INPUT_FILE);