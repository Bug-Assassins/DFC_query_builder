#include "declarations.h"
#include "file_handler.h"
#include "create.h"
#include "utility.h"
#include "bptree.h"
#include "insert.h"
#include "select.h"

void
run_server(int port_num)
{
    char *data, *line, *start, *tabname, *temp_char;
    struct sockaddr_in recaddr, senaddr;
    int i, recfd, senfd, clength, dlength, query_num, no_of_cols, temp;
    bool server_run = true;
    struct cols column[MAX_ATT];

    data = (char *) malloc(sizeof(char) * (MAX_MESSAGE_LEN + 1) );
    tabname = (char *) malloc(sizeof(char) * (MAX_NAME + 1) );
    start = data;

    recfd = socket(AF_INET, SOCK_STREAM, 0);
    bzero(&recaddr, sizeof(recaddr));
    recaddr.sin_family = AF_INET;
    recaddr.sin_port = htons(port_num);

    bind(recfd, (struct sockaddr *) &recaddr, sizeof(struct sockaddr_in));
    listen(recfd, 1);
    clength = sizeof(struct sockaddr_in);

    if(SERVER_OUTPUT)
    {
        printf("Server Started .... ... !!\n");
        fflush(stdout);
    }

    while (server_run)
    {
        if (SERVER_OUTPUT)
        {
            printf("Waiting for Connection .. .... ...\n");
            fflush(stdout);
        }
        senfd =
            accept(recfd, (struct sockaddr *) &senaddr,
                   (socklen_t *) & clength);

        if (SERVER_OUTPUT)
        {
            printf("Waiting for query data .... .... ...\n");
            fflush(stdout);
        }
        dlength = recv(senfd, data, MAX_MESSAGE_LEN - 1, 0);

        data[dlength] = '\0';
        //printf("\nDATE RECEIVED:\n%s\nDATA END\n", data);
        line = strchr(data, '\n');
        sscanf(data, "%d", &query_num);
        data = line + 1;
        switch (query_num)
        {
            case CREATE:
                if (SERVER_OUTPUT)
                {
                    printf("CREATE TABLE Requested !!\n");
                    fflush(stdout);
                }
                sscanf(data, "%s %d", tabname, &no_of_cols);
                for (i = 0; i < no_of_cols; i++)
                {
                    line = strchr(data, '\n');
                    data = line + 1;
                    sscanf(data, "%s %d %d", column[i].col_name, &column[i].type,
                           &column[i].size);
                    //printf("%s %d %d\n", column[i].col_name, column[i].type,
                           //column[i].size);
                }

                temp = CREATE_command(tabname, no_of_cols, column);
                if (temp)
                {
                    printf("Error in creating table: %s", tabname);
                }
                break;

            case SELECT:
                if (SERVER_OUTPUT)
                {
                    printf("SELECT QUERY Requested !!\n");
                    fflush(stdout);
                }
                select_query(data, senfd);
                break;

            case INSERT:
                if (SERVER_OUTPUT)
                {
                    printf("INSERT QUERY Requested !!\n");
                    fflush(stdout);
                }
                sscanf(data,"%s %d", tabname, &no_of_cols);
                void *value[MAX_ATT];
                int col[MAX_ATT];
                assert(no_of_cols <= MAX_ATT);
                for( i = 0; i < no_of_cols; i++)
                {
                    line = strchr(data, '\n');
                    data = line + 1;

                    sscanf(data,"%d", &temp);
                    if(temp == INTEGER)
                    {
                        value[i] = malloc(sizeof(int));
                        sscanf(data,"%d %d %d",&temp, &col[i], (int *)value[i]);
                        //printf("%d %d %d\n", temp, col[i], *(int *)value[i]);
                    }
                    else if(temp == VARCHAR)
                    {
                        value[i] = malloc(sizeof(char) * (MAX_NAME + 1));
                        sscanf(data,"%d %d %s", &temp, &col[i], (char *)(value[i]));
                        //printf("%d %d %s\n", temp, col[i], (char *)(value[i]));
                    }
                    else if (temp == DATE)
                    {
                        value[i] = malloc(sizeof(char) * (DATE_LENGTH + 1));
                        sscanf(data,"%d %d %s",&temp, &col[i], (char *)(value[i]));
                        //printf("%d %d %s\n", temp,  col[i], (char *)(value[i]));
                    }
                    else
                    {
                        printf("Wrong Category\n");
                    }
                }

                temp = INSERT_command(tabname, col, value, no_of_cols);
                if (temp)
                {
                    printf("Error INSERTING\n");
                }
                for (i = 0; i < no_of_cols; i++)
                    free(value[i]);

                send(senfd, "1",1, 10);
                break;

            case DROP:
                if (SERVER_OUTPUT)
                {
                    printf("DROP TABLE Requested !!\n");
                    fflush(stdout);
                }
                temp_char = (char *)malloc(500 * sizeof(char));
                sscanf(data, "%s", tabname);
                sprintf(temp_char, "rm -R ./table/%s", tabname);
                system(temp_char);
                sprintf(temp_char, "grep -v \"%s\" ./table/table_list > ./table/temp; mv ./table/temp ./table/table_list", tabname);
                system(temp_char);

                free(temp_char);
                break;

            case SHOW:
                if (SERVER_OUTPUT)
                {
                    printf("SHOW TABLE Requested !!\n");
                    fflush(stdout);
                }

                /*** Send the table count followed by names Meta db file (Testing)***/
                temp_char = show_table();
                /****************************/
                send(senfd, temp_char, strlen(temp_char), 10);
                free(temp_char);
                break;

            case DESC:
                if (SERVER_OUTPUT)
                {
                    printf("DESCRIBE TABLE Requested !!\n");
                    fflush(stdout);
                }
                sscanf(data, "%s", tabname);

            /*** Send the meta table details (Testing)***/
                temp_char = describe_table(tabname);
                send(senfd, temp_char, strlen(temp_char), 10);
            /*****************************/
                free(temp_char);
                break;

            case CLOSE_SERVER:
                if (SERVER_OUTPUT)
                {
                    printf("SERVER CLOSE Requested !!\n");
                    fflush(stdout);
                }
                server_run = false;
                break;
            default:
                if (SERVER_OUTPUT)
                {
                    printf("Error : Query not Recognized !!\n");
                    fflush(stdout);
                }
                break;
        }
        if (SERVER_OUTPUT)
        printf("Query Served !!\n");
        fflush(stdout);
        data = start;
        shutdown(senfd, 2);
        close(senfd);

    }
    shutdown(recfd, 2);
    close(recfd);

    free(tabname);
    free(data);
}
int
main(int argc, char *argv[])
{
    int port = atoi(argv[1]);
    if(SERVER_OUTPUT)
    {
        printf("Running Server on Port No : %d\n\n", port);
        fflush(stdout);
    }
    run_server(port);
    return 0;
}
