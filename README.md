# lab2 
# Реализация шаблона CRUD
------

## Задача
Разработать и реализовать клиент-серверную информационную систему, реализующую мехнизм CRUD. Система представляет собой веб-страницу с лентой постов и форму добавления нового поста.
#### Реализовать возможности системы:
 -  Добавление постов в общую ленту
 -  Реагирование на чужие посты (лайки)
 -  Комментирование чужих постов
 -  Ответы на комментарии под постами (комментирование второго уровня)

## Ход работы

### 1. Разработка пользовательского интерфейса

Разработанный дизайн форума:

<p align = "center"><img src="https://github.com/Sa2ap/lab2/blob/main/Site1.PNG"/width = 100%></p>

### 2. Описание пользовательских сценариев работы
На сайте доступны следующие возможности:
- Создание коментария
- Реагирование на существующие записи (лайки)
- Комментирование записей и ее комментариев
- Перемещение по форуму для чтения старых постов

1) После нажатия пользователем кнопки "Write comment" появляеться окно для написания коментария. если поле не заполнено появиться сообщение об этом.

2) Пользователь после нажатия на кнопку "Submit Post", страница форума обновится и среди всех записей появится его новая запись. Если поле не заполнено появиться сообщение об этом.

3) Пользователь может оставить реакции (лайк), количество реакций от одного пользователя не ограничено. 

4) Пользователь может прокомментировать запись или комментарий первого уровня под ней.

### 3. Описание API сервера и хореографии
#### HTTP запросы:

- Запрос на пост новой записи (на оставление комментария аналогичный):
<p align = "center"><img src="https://github.com/Sa2ap/lab2/blob/main/Site5.PNG"/width = 600></p>


### 4. Описание структуры базы данных

#### Для хранения данных форума используется база данных MySQL. 

Всего в базе данных содержится 3 таблицы: 
Таблица *comment*
| Название | Тип | Длина | По умолчанию | Описание |
| :------: | :------: | :------: | :------: | :------: |
| **id** | INT  |  | NO | Автоматический идентификатор поста |
| **page_id** | INT | 3000 | NO | Родительский айди |
| **parent_id** | INT|  | NO | Дочерний айди |
| **name** | VARCHAR |  | 0 | Имя автора |
| **content** | TEXT | 255 | NO | Содержание |
| **submit_date** | DATATIME | 255 | NO | Дата |
| **likes** | INT | 255 | NO | Лайки |



### 5. Описание алгоритмов (блок-схемы)

- Добавление нового поста:
<p align = "center"> <img src="https://github.com/Sa2ap/lab2/blob/main/add.png" width = "700"> </p>


## Значимые фрагменты кода

Код css:
```sh
   .comment {
  transform: translateX(120px);
}
.comments .comment_header {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px solid #eee;
    padding: 15px 0;
    margin-bottom: 10px;
    align-items: center;
    transform: translateX(20px);
}
.comments .comment_header .total {
    color: #777777;
    font-size: 14px;
}
.comments .comment_header .write_comment_btn {
    margin: 0;
}
```
Показывание коментариев:
```sh
function show_write_comment_form($parent_id = -1) {
    $html = '
    <div class="write_comment" data-comment-id="' . $parent_id . '">
        <form>
            <input name="parent_id" type="hidden" value="' . $parent_id . '">
            <input name="name" type="text" placeholder="Your Name" required>
            <textarea name="content" placeholder="Write your comment here..." required></textarea>
            <button type="submit">Submit Comment</button>
        </form>
    </div>
    ';
    return $html;
}
if (isset($_GET['page_id'])) {
    // Check if the submitted form variables exist
    if (isset($_POST['name'], $_POST['content'])) {
        // POST variables exist, insert a new comment into the MySQL comments table (user submitted form)
        $stmt = $pdo->prepare('INSERT INTO comments (page_id, parent_id, name, content, submit_date) VALUES (?,?,?,?,NOW())');
        $stmt->execute([ $_GET['page_id'], $_POST['parent_id'], $_POST['name'], $_POST['content'] ]);
        exit('Your comment has been submitted!');
    }
    // Get all comments by the Page ID ordered by the submit date
    $stmt = $pdo->prepare('SELECT * FROM comments WHERE page_id = ? ORDER BY submit_date DESC');
    $stmt->execute([ $_GET['page_id'] ]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Get the total number of comments
    $stmt = $pdo->prepare('SELECT COUNT(*) AS total_comments FROM comments WHERE page_id = ?');
    $stmt->execute([ $_GET['page_id'] ]);
    $comments_info = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    exit('No page ID specified!');
}

?>
```
Код index.html:
```sh
<script>
const comments_page_id = 1; // This number should be unique on every page
fetch("comments.php?page_id=" + comments_page_id).then(response => response.text()).then(data => {
	document.querySelector(".comments").innerHTML = data;
	document.querySelectorAll(".comments .write_comment_btn, .comments .reply_comment_btn").forEach(element => {
		element.onclick = event => {
			event.preventDefault();
			document.querySelectorAll(".comments .write_comment").forEach(element => element.style.display = 'none');
			document.querySelector("div[data-comment-id='" + element.getAttribute("data-comment-id") + "']").style.display = 'block';
			document.querySelector("div[data-comment-id='" + element.getAttribute("data-comment-id") + "'] input[name='name']").focus();
		};
	});
	document.querySelectorAll(".comments .write_comment form").forEach(element => {
		element.onsubmit = event => {
			event.preventDefault();
			fetch("comments.php?page_id=" + comments_page_id, {
				method: 'POST',
				body: new FormData(element)
			}).then(response => response.text()).then(data => {
				element.parentElement.innerHTML = data;
			});
		};
	});
});
</script>
	</body>
</html>
```
