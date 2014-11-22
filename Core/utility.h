#include "declarations.h"
void *ptr_to_index(int index, int unit_size, void *ptr)
{
    return (void *)((char *)ptr + index * unit_size);
}

// For easy increment
#define ptr_increment(unit_size, ptr) ptr_to_index(1, unit_size, ptr)

void reset_fp(struct table *t_ptr)
{
    rewind(t_ptr->fp);
}
// blocks are zero indexed
int read_into_buf(struct table *t_ptr, int block)
{
    FILE *fp;
    int countt;
    blockbuf_flush(t_ptr);
    //printf("Inside read_into_buf %s\n", t_ptr->name);

    if (t_ptr->is_temp)
    fp = open_file(t_ptr->tname, 2, const_cast<char*>("r"), t_ptr->is_temp);
    else
    fp = open_file(t_ptr->name, 2, const_cast<char*>("r"), t_ptr->is_temp);

    fseek(fp, t_ptr->BLOCKSIZE * block, SEEK_SET);
    t_ptr->dirty = 0;
    t_ptr->blocknum = block;
    countt = fread(t_ptr->blockbuf, 1, t_ptr->BLOCKSIZE, fp);
    fclose(fp);
    //printf("Returning from read_into_buf\n");
    return countt;
}
void *get_rec(struct table *t_ptr, int rec_num)
{
    //printf("Requested rec_num:%d from %s\n", rec_num, t_ptr->name);
    int index;
    int blocknum = (rec_num - 1) / MULTIPLICITY; // Gives the blocknum 0 indexed
    //printf("Its there in %d block\n", blocknum);

    //if (t_ptr->blocknum != blocknum)
        read_into_buf(t_ptr, blocknum);

    index = rec_num % MULTIPLICITY;
    if (!index)
        index = MULTIPLICITY;

    return ptr_to_index(index - 1, t_ptr->size, t_ptr->blockbuf);
}
int operate(void *left, void *right, int op, int type)
{
    int r = 0, value;
    if (type == VARCHAR)
    {
        value = strcmp((char *)left, (char *)right);
        //printf("Comparing %s with %s\n", (char *)left, (char *)right);
    }
    else
    {
        value = *(int *)left - *(int *)right;
        //printf("Comparing %d with %d\n", *(int *)left, *(int *)right);
    }

    switch(op)
    {
        case EQUAL:
            if (value == 0)
                r = 1;
            break;

        case GREAT_EQ:
            if (value >= 0)
                r = 1;
            break;

        case LESS_EQ:
            if (value <= 0)
                r = 1;
            break;

        case NOT_EQ:
            if (value != 0)
                r = 1;
            break;

        case GREAT:
            if (value > 0)
                r = 1;
            break;

        case LESS:
            if (value < 0)
                r = 1;
            break;
    }
    //printf("In operator, Status = %d\n", r);
    return r;
}
void * attribute(struct table *t_ptr, void *record, int n)
{
    return (void *)((char *)record + t_ptr->prefix[n]);
}
/*
    int col[] is the list of col whose value has been given by the user.
    void *value[] is the value given by the user. Will point to int if INTEGER else char *
    int size is the number of columns the user has set some values
*/
void *create_rec(struct table *t_ptr, int col[], void *value[], int size)
{
    //printf("Inside create_rec %d\n", size);
    int i, len;
    void *save = malloc(t_ptr->size);
    memset(save, 0, t_ptr->size);
    for (i = 0; i < size; i++)
    {
        //printf("%d %d\n", i, col[i]);
        switch(t_ptr->col[col[i]].type)
        {
            case INTEGER:
            //printf("INTEGER\n");
            *(int *)attribute(t_ptr, save, col[i]) = *(int *)(value[i]);
           // printf("%d ", *(int *)attribute(t_ptr, save, col[i]));
                break;

            case VARCHAR:
            //printf("VARCHAR\n");
            len = strlen((char *)value[i]);
            len = MIN(len, MAX_VARCHAR);
            strncpy((char *)attribute(t_ptr, save, col[i]), (char *)value[i], len);
            //printf("\"%s\"", (char *)attribute(t_ptr, save, col[i]));
                break;

            case DATE:
            //printf("DATE\n");
            len = strlen((char *)value[i]);
            len = MIN(len, DATE_LENGTH);
            strncpy((char *)attribute(t_ptr, save, col[i]), (char *)value[i], len);
            //printf("\"%s\"", (char *)attribute(t_ptr, save, col[i]));
                break;
        }
    }
    //printf("\nOutside create_rec\n");
    return save;
}
void print_table(struct table *t_ptr)
{
    void *temp, *t;
    int i, j, rec_count, block_count;


    if (!SERVER_OUTPUT)
        return;

    block_count = 0;
    rec_count = 0;
    printf("-----------Printing table: %s---------------\n", t_ptr->name);
    while (read_into_buf(t_ptr, block_count++) == t_ptr->BLOCKSIZE)
    {
        temp = t_ptr->blockbuf;
        for (i = 0; i < MULTIPLICITY && rec_count < t_ptr->rec_count; i++, rec_count++)
        {
            for (j = 0; j < t_ptr->count; j++)
            {
                t = attribute(t_ptr, temp, j);
                switch (t_ptr->col[j].type)
                {
                    case INTEGER:
                    printf("%d ", *((int *)t));
                    break;
                    case VARCHAR:
                    printf("%s ", (char *)t);
                    break;
                    case DATE:
                    printf("%s ", (char *)t);
                    break;
                }
            }
            printf("\n");
            temp = ptr_increment(t_ptr->size, temp);
        }
    }
}
