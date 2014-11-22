#ifndef DEFINITIONS
#define DEFINITIONS 1
#include <netinet/in.h>
#include <math.h>
#include <stdint.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/socket.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <time.h>
#include <unistd.h>
#include <assert.h>
#include <limits.h>

#include <vector>
#include <algorithm>
#include <iostream>
#include <string>
#include <fstream>
#include <stack>

// BPTREE

#define BPTREE_MAX_FILE_PATH_SIZE 1000
#define BPTREE_MAX_KEYS_PER_NODE 30
#define BPTREE_INSERT_SUCCESS 1
#define BPTREE_INSERT_ERROR_EXIST 2
#define BPTREE_SEARCH_NOT_FOUND -1

#define MAX_MESSAGE_LEN 10000

// DATA TYPES
#define INTEGER 1
#define VARCHAR 2
#define DATE 3

#define DATE_LENGTH 15
#define MAX_NAME 30
#define MAX_ATT 32
#define MAX_VARCHAR 50
#define MULTIPLICITY 10
#define MAX_MESSAGE_LEN 10000

// Operations
#define EQUAL 1
#define GREAT_EQ 2
#define LESS_EQ 3
#define NOT_EQ 4
#define GREAT 5
#define LESS 6

// COMMANDS
#define CSTART 100
#define CEND 101

#define CREATE 1
#define SELECT 2
#define WHERE 3
#define INSERT 5
#define DROP 6
#define DISTINCT 7
#define SHOW 8
#define DESC 9
#define CLOSE_SERVER 10

#define MAX(a, b) (((a) > (b))? (a): (b))
#define MIN(a, b) (((a) < (b))? (a): (b))

#define PORTNO 7080
#define bool int
#define true 1
#define false 0
#define SERVER_OUTPUT 0
/*
    first module (parser)

    insert record
    find_brute
    find_b+tree key
    create table
    select (not multiple but one by one on a temp table)
    SHOW TABLES;

    choose column --
                    | - > Together its RA_project
    DISTINCT rows --
    drop table
    Describe table;


    JOIN

    UPDATE TABLE

    interface definitions DONE


    TODO:
    Change the method of insertion of record. Should not just append.
*/

struct cols
{
    char col_name[MAX_NAME + 1];
    int type;
    int size;
};

struct table{

// Initialised during creation
    char name[MAX_NAME + 1];          // Name of the table
    struct cols col[MAX_ATT];
    int count;              // Number of attributes

    int size;               // Size of a record. Set it by calling record_size();
    int prefix[MAX_ATT + 1];
    int BLOCKSIZE;          // BLOCKSIZE is record size * MULTIPLICITY currently.
    int is_temp;            // Temporary table or not
    char tname[MAX_NAME + 1];



// Runtime changing variables
    FILE *fp;               // .rec file
    FILE *fpt;              // .met file
    void *blockbuf;         // Will store the pointers to the blocks of table loaded
    int dirty;              // Initialise to 0
    int blocknum;           // Will store the id of the block in the blockbuf (i.e nth block)
    int rec_count;          // Number of records currently in the table

/*
If rec_count changes then immediately after the command update the table meta file
*/
};
int fname = 0;
#endif
