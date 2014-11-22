DFC Query Builder
=================

DFC Query Builder is a mini scale database management system. Originally planned as a SQL query builder, the project evolved into something more.

This project was done as a part of _Database Systems_ course in V<sup>th</sup> semester B.Tech program in Department of Information Technology, National Institute of Technology Karnataka, Surathkal during the academic year 2014.

Queries Supported
=================

Queries works exactly like real-life database management systems MySQL. As of now following queries are supported :

1. CREATE Table
2. SHOW Tables
3. DESCRIBE Table
4. DROP Table
5. INSERT INTO Table
6. SELECT Table


GUI
===

We have provided a simple web-based GUI for the user to input the query. The user just have to make a few clicks and enter only the relevant part of the query. Our system generates an equivalent SQL query for the requested operation. Users can also view the SQL Query being executed.

Database Core
=============

Our Database Management System is implemented using C++ which receives messages from the front-end Web Server (Coded using PHP) using Sockets. This C++ program acts as a live database server which listens for query and returns the result. It stores the data on physical disk using files. The main highlights of our backend are as follows :

1. __Custom Blocks for each Table__ – It is always a multiple of record size. This saves space as well as overhead of checking for waste space. Our system reads data from and writes data to disk in blocks.
2. __B+ Tree Indexing__ – A separate multilevel B+ Tree for each table. Each node of the B+ Tree contains around 30 entries. It is used to index primary key. Right now the system supports only primary key indexing and all data type for primary key should be an integer. The Indexing reduces the time complexity of searching for a record.
3. __Storing Meta Data of tables__ – Meta Data of the tables are stored separately which provides quick access to several information like current number of records, table name, B+ tree indexing dat, etc.
4. __Temporary Tables__ – We use temporary table to store intermediate results obtained from any query. However, user generated temporary table is currently not supported. The system applies the conditions specified in SELECT Query's WHERE clause incrementally one by one.
5. __Operators__ - 6 relational operators namely - (>=, <=, >, <, =, !=) and one Logical Operator – (AND) is currently supported by our system.
6. __Brute Search and B+ Tree Indexed Search__ -  Both methods implemented to search for a record in the file. Brute Force is used in cases where the primary key indexing cannot be used to fetch record.

Installation
============

__Pre-requisites__ : Linux, A C++ Compiler, A Web Server (Apache)

__Getting Started__: 

1. User has to host the Web_Server Folder and access the "index.php" file. 
2. User has to compile the "main.cpp" program in the Core folder using any standard C++ compiler (G++ is recommended).
3. After compiling the program the user should execute the program passing the desired port number as a command line argument on which the server should run.
4. Once the server starts running, the same port number and the system's IP address can be entered in the Login Screen of PHP. The user and password both are hard-coded at "root".
5. Once Logged in, the user can execute the queries as listed on the side-pane.
6. Users can monitor the working of DBMS core on the terminal.

Project Contributors
====================

The following members contributed equally to this project :

1. Adarsh Mohata - amohta163@gmail.com - Git : https://github.com/Adarsh1994
2. Ajith P S - ajithpandel@gmail.com - Git : https://github.com/ajithps
3. Ashish Kedia - ashish1294@gmail.com - Git : https://github.com/ashish1294

~ a.k.a Team **Bug Assassins**
