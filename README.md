# alab
recruitment task

## Podsumowanie zadania
Zadanie zajęło 5 dni roboczych czyli 1,2 story pointa na dzień :)
Patrząc na wszystkie zadania chyba lepiej byłoby zacząć je od końca. Uniknąłbym wtedy problemów z dockerem i CI/CD. 
Rozpocząłem prace na bazie Sqlite i środowisku lokalnym artisan. Potem wszystko przeniosłem do Dockera i MySQL.  Dlatego też projekt można uruchomić na docker a testy w CI wykonują się na sqlite.

Posumuje zadania po kolei:

## Zadanie1
Import pacjentów działa i jest pokryty testami. 
W zadaniu przyjąłem że pacjenci muszą być w systemie a zamowienie niekoniecznie. Dlatego też jak nie ma użytkownika rekord jest pomijany. Gdy nie ma zamówienia jest tworzone nowe z nowym id.
Teraz bym zrobił to inaczej przyjmując inne podejście do zamówień i pacjentów. Tworzył bym pacjentów tym bardziej ze są wszystkie dane. No ale to już kwestia bardziej wymagań.
W CSV brakowało mi kolumny jednoznacznie identyfikującej rodzaj badania ponieważ kilka badań mogło należeć do tego samego zamówienia. Można by było w ten sposób uniknąć problemów z duplikatami.

## Zadanie2
API działa i jest pokryte testami. Zmieniłem sposób wyświetlania błędów w przypadku zapytań do API aby zwracały komunikaty w formacie JSON a nie HTML.

## Zadanie3
Tu się troche zagubiłem z tym JWT. Zrobiłem to tak że logowanie jest na sesji a po przekierowaniu na dashboard token jest zapisywany w localStorage. Jeżeli user nie wykonuje żadnych akcji przez dłuższy czas sprawdzana jest ważność tokenu i w przypadku wygaśnięcia użytkownik jest wylogowywany. Co do wyników badań to wyświetliłem na szybko wszystkie wyniki pacjenta ale lepiej było by je pogrupować na zamówienia i inaczej przejść przez relacje w modelu.

## Zadanie4
Baza danych została stworzona na tyle ile było mi to potrzebne do zadania. Do bazy zostały dodane seedery i factory. 

## Zadanie5
CI/CD działa i sprawdza testy (na bazie sqllite). Buduje się obraz dockera. Czyszczony jest kod Laravel Laravel Pint i sprawdzany jest kod pod kątem jakości Larastan.

## Zadanie6
Tu wykazałem się bardziej skutecznością niż kreatywnością :) i wykorzystałem gotowe rozwiązanie z Laravela Sail. Zbudowałem obraz dockera i uruchomiłem kontenery i po wykonaniu migracji i seederów wewnątrz kontenera aplikacja działa i można sie bylo zalogować. Testy działają także w kontenerze dockera.
