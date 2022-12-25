# lab2 
# Реализация шаблона CRUD
------

## Задача
Разработать и реализовать клиент-серверную информационную систему, реализующую мехнизм CRUD. Система представляет собой веб-страницу с лентой постов и форму добавления нового поста.
#### Реализовать возможности системы:
 -  Добавление постов в общую ленту (текст и 1 поясняющая картинка, если необходимо)
 -  Реагирование на чужие посты (лайки, дизлайки...)
 -  Комментирование чужих постов
 -  Ответы на комментарии под постами (комментирование второго уровня)
 -  "Раскрывающиеся" комментарии

## Ход работы

### 1. Разработка пользовательского интерфейса

Разработанный дизайн форума:

<p align = "center"><img src="https://github.com/Ponixs/online_forum/blob/main/for%20readme/Общий_вид.png"/width = 100%></p>

### 2. Описание пользовательских сценариев работы
На сайте доступны следующие возможности:
- Публикация записей с прикреплением картинки
- Реагирование на существующие записи (лайки...)
- Комментирование записей и ее комментариев
- Перемещение по форуму для чтения старых постов

1) Если пользователь введет в посте более 3000 символов или менее 4, или прикрепит картинку более 2 МБ, или картинка будет не jpeg(jpg), то будет выведено сообщение об ошибке.

2) Если пользователь не допустил ошибок при добавлении поста, то после нажатия на кнопку "добавить", страница форума обновится и среди всех записей появится его новая запись.

3) Пользователь может оставить реакции (лайк, дизлайк), количество реакций от одного пользователя не ограничено. Но само количество реакций ограничено 999.

4) Пользователь может прокомментировать запись или комментарий первого уровня под ней. Объем комментария от 4 до 1000 символов, иначе появится сообщение об ошибке. Если объем комментария соответствует условиям, то после нажатия на кнопку "Оставить комментарий", страница форума обновится и к записи добавится новый комментарий.


### 3. Описание API сервера и хореографии
#### HTTP запросы:

- Запрос на пост новой записи (на оставление комментария аналогичный):
<p align = "center"><img src="https://github.com/Ponixs/online_forum/blob/main/for%20readme/создание_новой_записи.png"/width = 600></p>


### 4. Описание структуры базы данных

#### Для хранения данных форума используется база данных MySQL. 

Всего в базе данных содержится 3 таблицы: 
Таблица *posts*
| Название | Тип | Длина | По умолчанию | Описание |
| :------: | :------: | :------: | :------: | :------: |
| **id** | INT  |  | NO | Автоматический идентификатор поста |
| **text** | VARCHAR | 3000 | NO | Текст поста |
| **dtime** | INT|  | NO | Дата создания поста |
| **likes** | INT |  | 0 | Количество лайков |
| **image** | VARCHAR | 255 | NO | Путь к файлу |

Таблица *comments*
| Название | Тип | Длина | NULL | Описание |
| :------: | :------: | :------: | :------: | :------: |
| **id** | INT  |  | NO | Автоматический идентификатор лайка |
| **text** | VARCHAR | 1000 | NO | текст комментария |
| **postid** | INT |  | NO | ID поста |

Таблица *sub_comments*
| Название | Тип | Длина | NULL | Описание |
| :------: | :------: | :------: | :------: | :------: |
| **id** | INT  |  | NO | Автоматический идентификатор лайка |
| **text** | VARCHAR | 1000 | NO | текст подкомментария |
| **postid** | INT |  | NO | ID поста |

### 5. Описание алгоритмов (блок-схемы)

- Добавление нового поста:
<p align = "center"> <img src="https://github.com/Ponixs/online_forum/blob/main/for%20readme/добавление_поста.png" width = "700"> </p>


- Добавление комментариев:
<p align = "center"><img src="https://github.com/Ponixs/online_forum/blob/main/for%20readme/добавление_комментария.png" width = "700"/></p>



- Переключение между страницами:
<p align = "center"><img src="https://github.com/Ponixs/online_forum/blob/main/for%20readme/переход_по_страницам.png" width = "700"/></p>

## Значимые фрагменты кода

Код получение комментария и записи его в базу данных:
```sh
   if (isset($_POST["text"])) {	
		$text = $_POST["text"];
		$time = time();
		$postid = $_POST["postid"];
		$page = $_POST["forum_page"];
	
		if (strlen($text) > 1000) {
			header("Location: index.php?message=Слишком длинный текст");
			exit();
		}
		
		if (strlen($text) < 4) {
			header("Location: index.php?message=Слишком короткий текст");
			exit();
		}
	
		$sql = "INSERT INTO `comments` (`text`, `postid`) VALUES ( '$text', '$postid')";
		
		mysqli_set_charset($link, "utf8");
		$res = mysqli_query($link, $sql);
	
		header("Location: index.php?page=".$page);
		exit();
	}
```
Парсинг постов, комментариев и комментариев второго уровня:
```sh
function parse_post($index, $result) {
	
	$data_string = $result[$index];
	
	$text = $data_string['text'];
	$likes = $data_string['likes'];
	$dislikes = $data_string['dislikes'];
	
	$postid = $data_string['id'];
			
	if(strlen($data_string['image']) == 10	)
		$image = $data_string['image'];
	else
		$image = "none";
	
	return [$text, $likes, $dislikes, $postid, $image];
}
function parse_comment($postid, $comments_db) {
	$comm_count = 0;
	$comments_a = array();
	$comments_id = array();
		
	foreach ($comments_db as $comment) {
		if ($comment["postid"] == $postid) {
			
			$comm_count += 1;
				
			$comments_a[$comm_count] = $comment["text"];
			$comments_id[$comm_count] = $comment["id"];
				
		}
	}
		
	return [$comments_a, $comm_count, $comments_id, 0];
}
function parse_sub_comments($comment_id, $sub_comments_db) {
	$sub_comm_count = 0;
	$sub_comments_a = array();
	
	foreach ($sub_comments_db as $sub_comment) {
		if ($sub_comment["comment_id"] == $comment_id) {
			
			$sub_comm_count += 1;
				
			$sub_comments_a[$sub_comm_count] = $sub_comment["text"];
		}
	}
	
	return [$sub_comments_a, $sub_comm_count];
}
```
Код раскрывающихся комментариев:
```sh
<script> 
	function show_comments(id){
		let c = document.getElementById("c"+id);
		c.removeAttribute("hidden");
		
		let b = document.getElementById("b"+id);
		b.textContent = "Скрыть комментарии";
		
		b.setAttribute("onClick", "hide_comments('"+id+"')");
	}
	
	function hide_comments(id) {
		let c = document.getElementById("c"+id);
		c.setAttribute("hidden", true);
		
		let b = document.getElementById("b"+id);
		b.textContent = "Показать комментарии";
		
		b.setAttribute("onClick", "show_comments('"+id+"')");
	}
</script>
```
