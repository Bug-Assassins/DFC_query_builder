#include "declarations.h"

void insert_one(struct table *t_ptr, void *rec)
{
    FILE *fp;
    //printf("Insert_one\n");
    int blocknum = (t_ptr->rec_count - 1)/ MULTIPLICITY;
    int rec_num;
    if (t_ptr->blocknum != blocknum)
    {
       // printf("Inside if\n");
        blockbuf_flush(t_ptr);
        if (t_ptr->rec_count % MULTIPLICITY == 0)
        {
            if (t_ptr->is_temp)
            fp = open_file(t_ptr->tname, 2, const_cast<char*>("a"), t_ptr->is_temp);
            else
            fp = open_file(t_ptr->name, 2, const_cast<char*>("a"), t_ptr->is_temp);

            memset(t_ptr->blockbuf, 0, t_ptr->BLOCKSIZE);
            fwrite(t_ptr->blockbuf, 1, t_ptr->BLOCKSIZE, fp);
            fclose(fp);
            t_ptr->blocknum = blocknum + 1;
        }
        else
            read_into_buf(t_ptr, blocknum);
        //printf("if done\n");
    }
    rec_num = t_ptr->rec_count % MULTIPLICITY;
    memcpy(((char *)t_ptr->blockbuf + rec_num * t_ptr->size), rec, t_ptr->size);
    t_ptr->rec_count++;
    t_ptr->dirty = 1;
    //printf("Inserted one\n");
}
int INSERT_command(char table_name[], int col[], void *value[], int size)
{
    int ret;
    BPtree temp_bp(table_name);
    struct table *temp;
    void *rec;
    temp = load_struct(table_name);
    //printf("Loaded struct\n");
    temp->blockbuf = malloc(temp->BLOCKSIZE);
    read_into_buf(temp, 0);
    rec = create_rec(temp, col, value, size);

    ret = temp_bp.insert_record(*((int *)value[0]) , temp->rec_count + 1);
    if (ret == BPTREE_INSERT_ERROR_EXIST)
    {
        //printf("BPTREE_INSERT_ERROR_EXIST\n");
        return 1;
    }

   // printf("Created rec\n");
    // Check if already present or not;
    insert_one(temp, rec);
    blockbuf_flush(temp);

    free(rec);
    write_struct(temp);
    free(temp->blockbuf);
    free(temp);
    //printf("Returning from INSERT_command\n");
    return 0;
}
