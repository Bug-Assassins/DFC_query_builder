#include "declarations.h"

int record_size(struct table *t_ptr)
{
    int i, size = 0;
    t_ptr->prefix[0] = 0;
    for (i = 0; i < t_ptr->count; i++)
    {
        switch(t_ptr->col[i].type)
        {
            case INTEGER :
                t_ptr->prefix[i + 1] = sizeof(int) + t_ptr->prefix[i];
                size += sizeof(int);
                break;

            case VARCHAR :
                t_ptr->prefix[i + 1] = sizeof(char) * (MAX_VARCHAR + 1) + t_ptr->prefix[i];
                size += sizeof(char) * (MAX_VARCHAR + 1);
                break;

            case DATE :
                t_ptr->prefix[i + 1] = sizeof(char) * DATE_LENGTH + t_ptr->prefix[i];
                size += sizeof(char) * DATE_LENGTH;
                break;
        }
    }
    return size;
}

struct table *create_table(char name[], int count, struct cols col[], int is_temp)
{
    int length, i;
    struct table *temp;

    temp = (struct table *)malloc(sizeof(struct table));
    temp->fp = NULL;
    temp->blockbuf = NULL;


    // Not really necessary. Till now. Might be for join, temp_table or project;
    temp->dirty = 0;
    temp->blocknum = 0;

    // Initialises the main properties of table
    length = strlen(name);
    length = MIN(MAX_NAME, length);
    strncpy(temp->name, name, length);
    temp->name[length] = '\0';

    temp->count = count;
    for (i = 0; i < count; i++)
    {
        temp->col[i].size = col[i].size;
        temp->col[i].type = col[i].type;
        strncpy(temp->col[i].col_name, col[i].col_name, MAX_NAME);
        temp->col[i].col_name[MAX_NAME] = '\0';
    }

    temp->size = record_size(temp);
    //printf("temp->size = %d\n", temp->size);
    temp->is_temp = is_temp;
    temp->BLOCKSIZE = temp->size * MULTIPLICITY;

    if (is_temp)
    temp->blockbuf = malloc(temp->BLOCKSIZE);

    // Create a empty B+Tree root here add appropriate elements to struct table definition

    return temp;
}
struct table *create_temp_table(char name[], int count, struct cols col[])
{
    struct table *temp;

    fname = (fname + 1) % 10;

    temp = create_table(name, count, col, 1);

    if (temp == NULL) { //printf("create_table inside create_temp_table returned NULL\n"); 
    return NULL;}

    strcpy(temp->tname, temp->name);
    temp->name[0] = '0' + fname;
    temp->name[1] = '\0';

    setup_files(temp, 1);

    strcpy(temp->name, temp->tname);
    temp->tname[0] = '0' + fname;
    temp->tname[1] = '\0';

    temp->blockbuf = malloc(temp->BLOCKSIZE);
    temp->blocknum = 0;
    memset(temp->blockbuf, 0, temp->BLOCKSIZE);

    temp->dirty = 1;
    blockbuf_flush(temp);
    return temp;
}
int CREATE_command(char name[], int count, struct cols col[])
{
    char t_name[MAX_NAME + 1];
    struct table *temp;

    FILE *fp = fopen("./table/table_list", "r+");
    while (fscanf(fp, "%s", t_name) != EOF);
    {
        if(!strcmp(t_name, name))
        {

            return 1;
        }
    }
    fseek(fp, 0, SEEK_END);
    fprintf(fp, "%s\n", name);
    fclose(fp);
//struct table *create_table(char name[], int count, struct cols col[], int is_temp)
    temp = create_table(name, count, col, 0);

    if (temp != NULL)
    {
        setup_files(temp, 1);
        fclose(temp->fp);
        temp->blockbuf = malloc(temp->BLOCKSIZE);
        fp  = open_file(temp->name, 2, const_cast<char*>("w"), 0);
        fwrite(temp->blockbuf, 1, temp->BLOCKSIZE, fp);
        fclose(fp);

        write_struct(temp);
        free(temp->blockbuf);
        free(temp);
        return 0;
    }
    else
    {
        printf("create_table inside CREATE function returned NULL\n");
        return 1;
    }

}
// Returns the size of the record.
