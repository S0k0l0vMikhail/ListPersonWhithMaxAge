<?php

require_once "PDOAdapter.php";
require_once "MyLogger.php";

class Index {

    
    private $dsn = 'mysql:host=localhost;dbname=mysql';
    private $username = 'mysql';
    private $password = 'mysql';
    private $fileName = 'log.txt';
    private $db;
    private $errorLogger;

    function __construct()
    {
        $errorLogger = new MyLogger($this->fileName);
        $this->db = new PDOAdapter($this->dsn, $this->username, $this->password, $errorLogger);
        
    }

    // Определяем максимальный возраст
    public function getMaxAge()
    {
        $age = $this->db->execute('selectOne', 'SELECT max(age) FROM `person`');
            foreach ($age as $key => $value) {
                $title = (int) $value;
            }
        return $title;
    }

    // Получаем любую персону, у которой mother_id не задан и возраст меньше максимального. 
    // Так как таблица маленькая, то использовать rand + limit допустимо.
    public function getSomePerson()
    {
            
        $res = $this->db->execute('selectOne', "SELECT `lastname`, `firstname` FROM `person` 
        WHERE `mother_id` IS NULL AND `age` < (SELECT max(age) FROM `person`) ORDER BY RAND() LIMIT 1"); 
            
        return $res->lastname . " " . $res->firstname;
    }

    // Изменяем возраст на максимальный для персоны полученной из getSomePerson();
    public function setAge(string $text)
    {   
        $person = trim($text);
        $args = explode(" ", $person);
        if ((strpos($person, " ") !== false) && count($args) == 2) {
            $this->db->execute('execute', 'UPDATE `person` SET  `age` = ' . $this->getMaxAge() . ' 
            WHERE `lastname` = ? AND `firstname` = ?', $args);
        };
    }

    // Получаем список персон максимального возраста (фамилия, имя и возраст). 
    // Так как при выводе на страницу необхходимо отсортировать по возрастанию, добавил сортировку в SQL запрос.
    public function getlistPerson()
    {   
        $people = array();
        $res = $this->db->execute('selectAll', "SELECT `lastname`, `firstname`,`age` FROM `person` 
        WHERE `age` = (SELECT max(age) FROM `person`) ORDER BY `lastname` ASC, `firstname` ASC");
        for ($i=0; $i < count($res); $i++) { 
            $people += [    $i =>
                            [
                            'firstname' => $res[$i]->firstname,
                            'lastname'  => $res[$i]->lastname,
                            'age'       => $res[$i]->age 
                            ]
                        ];
        }
        return $people;
    }

};

$a = new Index();
$people = $a->getlistPerson();
$age = $a->getMaxAge();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Список персон максимального возраста <?php echo $age; ?></title>
</head>
<body>
    <div>
        <table>
            <tr><th>Фамилия</th><th>Имя</th><th>Возраст</th></tr>
            <?php   for($i=0; $i <= count($people) - 1; $i++):?>
                            <tr>
                            <td><?php echo $people[$i]['lastname'];?></td>
                            <td><?php echo $people[$i]['firstname'];?></td>
                            <td><?php echo $people[$i]['age'];?></td>
                            </tr>
            <?php   endfor; ?>
        </table>
    </div>
</body>
</html>