#include "declarations.h"
FILE *open_file(char t_name[], int option, char perm[], int is_temp)
{

    FILE *fp;
    struct stat st = {0};
    char *name = (char *)malloc(sizeof(char) * (2 * MAX_NAME + 15));

    if (is_temp)
        strcpy(name, "./tempd/");
    else
        strcpy(name, "./table/");

    if (stat(name, &st) == -1)
        mkdir(name, 0775);

    strcat(name, t_name);
    strcat(name, "/");

    if (stat(name, &st) == -1)
        mkdir(name, 0775);

    switch(option)
    {
        // met
        case 1:
        strcat(name, "met");
        //printf("Opening file: %s %d %s %d\n", name, option, perm, is_temp);
        fp = fopen(name, perm);
        break;

        // ret
        case 2:
        strcat(name, "rec");
        //printf("Opening file: %s %d %s %d\n", name, option, perm, is_temp);
        fp = fopen(name, perm);
        break;

        //printf("Open file called with wrong option\n");

    }
    if (!fp)
    {
       // printf("Error in opening file: %s %d %s %d\n", name, option, perm, is_temp);
    }
    free(name);
    return fp;
}
void setup_files(struct table *t_ptr, int is_new)
{
    printf("Inside setup_files\n");
    FILE *fp, *fpt;

    if (is_new)
        fp = open_file(t_ptr->name, 2, const_cast<char*>("w+"), t_ptr->is_temp);
    else
        fp = open_file(t_ptr->name, 2, const_cast<char*>("r+"), t_ptr->is_temp);

    if (!t_ptr->is_temp)
    {
        if (is_new)
            fpt = open_file(t_ptr->name, 1, const_cast<char*>("w+"), 0);
        else
            fpt = open_file(t_ptr->name, 1, const_cast<char*>("r+"), 0);

        if (!fpt)
        {
            printf("Error opening met file\n");
        }
        else
            fclose(fpt);
    }



    if (!fp)
    {
        printf("Error opening rec file\n");
    }

    t_ptr->fp = fp;
    //printf("Done in setup_files\n");
}

struct table *load_struct(char name[])
{
    struct table *temp;
    //printf("Loading table : \"%s\"\n", name);
    FILE *fp = open_file(name, 1, const_cast<char*>("r"), 0);
    temp = (struct table *)malloc(sizeof(struct table));
    fread(temp, sizeof(struct table), 1, fp);
    fclose(fp);
    //printf("Loaded table\n");
    return temp;
}
int write_struct(struct table *t_ptr)
{
    FILE *fp = open_file(t_ptr->name, 1, const_cast<char*>("w"), 0);
    fwrite(t_ptr, sizeof(struct table), 1, fp);
    fclose(fp);
    //printf("Struct updated\n");
    return 0;
}
void blockbuf_flush(struct table *t_ptr)
{
    FILE *fp;
    //printf("blockbuf_flush started\n");
    if (!t_ptr->dirty)
    {
        //printf("Not dirty, returning\n");
        return;
    }

    //printf("blocknum = %d\n", t_ptr->blocknum);
    if (t_ptr->is_temp)
    fp = open_file(t_ptr->tname, 2, const_cast<char*>("r+"), t_ptr->is_temp);
    else
    fp = open_file(t_ptr->name, 2, const_cast<char*>("r+"), t_ptr->is_temp);

    fseek(fp, t_ptr->BLOCKSIZE * (t_ptr->blocknum), SEEK_SET);
    fwrite(t_ptr->blockbuf, t_ptr->BLOCKSIZE, 1, fp);

    fclose(fp);

    t_ptr->dirty = 0;
   // printf("blockbuf_flush ended\n");
}

void add_to_list(char *name)
{
    char temp[MAX_NAME + 1];

    strcpy(temp, name);
    FILE *fp = fopen("./table/table_list", "a");
    fwrite(temp, MAX_NAME, 1, fp);

    fclose(fp);
}
char *show_table()
{
    char *names_temp = (char *)malloc(55 * MAX_NAME);
    int count;
    count = 0;
    sprintf(names_temp, "0             ;");
    char temp[MAX_NAME + 1];
    // While saving to the file save the whole name[MAX_NAME]; For proper division
    FILE *fp = fopen("./table/table_list", "r");
    while (fscanf(fp, "%s", temp) != EOF)
    {
        strcat(names_temp, temp);
        strcat(names_temp, ";");
        count++;
    }
    count = sprintf(names_temp, "%d", count);
    names_temp[count] = ' ';
    //printf("TABLE FORMAT:\n%s\n", names_temp);
    fclose(fp);
    return names_temp;
}
char *describe_table(char *name)
{
    int i;
    char number[10];
    char *send = (char *)malloc(MAX_NAME + MAX_ATT * (MAX_NAME + 5));
    struct table *temp = load_struct(name);
    sprintf(send, "%d\n", temp->count);
    for (i = 0; i < temp->count; i++)
    {
        strcat(send, temp->col[i].col_name);
        strcat(send, " ");
        sprintf(number, "%d", temp->col[i].type);
        strcat(send, number);
        strcat(send, " ");
        sprintf(number, "%d", temp->col[i].size);
        strcat(send, number);
        strcat(send, "\n");
    }
    //printf("%s\n", send);
    free(temp);
    return send;
}
