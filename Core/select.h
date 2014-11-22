#include "declarations.h"
struct table *key_find_record(struct table *t_ptr, std::vector<int> opp, std::vector<void *> rvalue, unsigned int size)
{
    unsigned int i;
    int max_ge, min_le, temp, temp_val;
    bool remain, flag;
    void *temp_rec;
    BPtree bp(t_ptr->name);
    table *temp_table;

    temp_table = create_temp_table(t_ptr->name, t_ptr->count, t_ptr->col);

    if(SERVER_OUTPUT)
    {
        printf("Indexing Found !!\n");
        printf("Optimizing SELECT Query !!\n");
        fflush(stdout);
    }

    std::vector<int> ge, le, ne;

    for(i = 0; i < size; i++)
    {
	    //printf(" opp [ %d] = %d\n", i, opp[i]);
        if(opp[i] == GREAT)
        {
            ge.push_back((*((int *)rvalue[i])) + 1);
        }
        else if(opp[i] == LESS)
        {
            le.push_back((*((int *)rvalue[i])) - 1);
        }
        else if(opp[i] == GREAT_EQ)
        {
            ge.push_back((*((int *)rvalue[i])));
        }
        else if(opp[i] == LESS_EQ)
        {
            le.push_back((*((int *)rvalue[i])));
        }
        else if(opp[i] == EQUAL)
        {
            ge.push_back((*((int *)rvalue[i])));
            le.push_back((*((int *)rvalue[i])));
        }
        else
        {
            ne.push_back((*((int *)rvalue[i])));
        }
    }
    //printf("Starting selection le size = %d ge = %d\n", le.size(), ge.size());fflush(stdout);
    if(ge.size() == 0)
    {
        ge.push_back(0);
    }

    if(le.size() == 0)
    {
        le.push_back(INT_MAX);
    }
    //printf("Starting selection2 ge size = %d \n", (int)ge.size());fflush(stdout);
    max_ge = ge[0];
    for(i = 1; i < ge.size(); i++)
    {
        if(ge[i] > max_ge) max_ge = ge[i];
    }
    //printf("Starting selection3 le size = %d \n", (int)le.size());fflush(stdout);
    min_le = le[0];
    for(i = 1; i < le.size(); i++)
    {
        if(le[i] < min_le) min_le = le[i];
    }

    if(SERVER_OUTPUT)
    {
        printf("SELECT Query Optimized !!\n");
        fflush(stdout);
    }

    Btreenode leaf = bp.search_leaf(max_ge);
    int pos = leaf.get_next_key(max_ge) + 1;

    //printf("Min = %d Max = %d\n", min_le, max_ge);
    //fflush(stdout);

    remain = true;
    while(remain)
    {
        //printf("pos = %d\n", pos);
        fflush(stdout);
        while(pos <= leaf.num_keys() && remain)
        {
            temp = leaf.get_pointer(pos);
            temp_rec = get_rec(t_ptr, temp);
            temp_val = *((int *)temp_rec);
            //printf("val = %d testing val = %d\n", leaf.get_key(pos), temp_val);
            ///fetch record no temp in temp_rec

            for(i = 0, flag = true; i < ne.size() && flag; i++)
            {
                ///check if temp_rec.column0 == ne[i]
                if(temp_val == ne[i])
                {
                    flag = false;
                   // printf("Breaking %d \n", i);
                }
            }

            if(flag)
            {
               // printf("Before inserting %d\n", leaf.get_key(pos));
                insert_one(temp_table, temp_rec);
                ///inser temp_rec to temp_table
            }

            if(temp_val >= min_le)
            {
                //printf("Reached min %d\n ", temp_val);
                remain = false;
            }
            pos++;

        }
        if(remain)
        {
            //printf("Rem true\n");
            if(leaf.get_next() != -1)
            {
                //printf("Next Node = %d\n", leaf.get_next());
                bp.read_node(leaf.get_next(), leaf);
                //printf("Leaf [ 0 ] = %d \n", leaf.get_key(1));
                pos = 1;
            }
            else
            {
                remain = false;
            }
        }
    }
    blockbuf_flush(temp_table);

    ge.clear();
    le.clear();
    ne.clear();

    return temp_table;
}

struct table *brute_find_record(struct table *t_ptr, int nth, std::vector<int>opp, std::vector<void*>rvalue, int size)
{
    void *temp, *value;
    int i = 0, j, rec_count = 0, block_count = 0, valid, type = t_ptr->col[nth].type;

    struct table *temp_table = create_temp_table(t_ptr->name, t_ptr->count, t_ptr->col);

    while (read_into_buf(t_ptr, block_count++) == t_ptr->BLOCKSIZE)
    {
        temp = t_ptr->blockbuf;
        for (i = 0; i < MULTIPLICITY && rec_count < t_ptr->rec_count; i++, rec_count++)
        {
            valid = 1;
            value = attribute(t_ptr, temp, nth);
            for (j = 0; j < size && valid; j++)
            {
                if (!operate(value, rvalue[j], opp[j], type))
                valid = 0;
            }
            if (valid)
            {
                insert_one(temp_table, temp);
            }
            temp = ptr_increment(t_ptr->size, temp);
        }
    }
    blockbuf_flush(temp_table);
    return temp_table;
}

void column_select(struct table *t_ptr, int *col_list, int col_count, int senfd)
{
    void *temp, *t;
    int i, j, rec_count, block_count;
    char *tuple = (char *)malloc(MAX_VARCHAR * (MAX_ATT + 5));
    char *tupleptr = tuple;
    int tcount;


    sprintf(tuple, "%-15d;%-14d\n", t_ptr->rec_count, MAX_VARCHAR * (col_count + 5));
    send(senfd, tuple, strlen(tuple), 10);
    memset(tuple, ' ', MAX_VARCHAR * (col_count + 5));
    for (i = 0; i < col_count; i++)
    {
       tcount = sprintf(tupleptr, "%s;", t_ptr->col[col_list[i]].col_name);
       tupleptr = tupleptr + tcount;
    }
    //printf("%s\n", tuple);
    // Send tuple;

    send(senfd, tuple, MAX_VARCHAR * (col_count + 5), 10);

    block_count = 0;
    rec_count = 0;
    t_ptr->dirty = 0;
    while (read_into_buf(t_ptr, block_count++) == t_ptr->BLOCKSIZE)
    {
        temp = t_ptr->blockbuf;

        for (i = 0; i < MULTIPLICITY && rec_count < t_ptr->rec_count; i++, rec_count++)
        {
            tcount = 0;
            tupleptr = tuple;
            memset(tuple, ' ', MAX_VARCHAR * (col_count + 5));
            //printf("Record number = %d\n", rec_count);
            for (j = 0; j < col_count; j++)
            {

                //printf("Cutting column %d\n", col_list[j]);
                t = attribute(t_ptr, temp, col_list[j]);
                switch (t_ptr->col[col_list[j]].type)
                {
                    case INTEGER:
                    //printf("INTEGER : %d\n", *((int *)(t)));
                    tcount = sprintf(tupleptr, "%d;", *((int *)(t)));
                    break;

                    case VARCHAR:
                    //printf("col = %d\n", col_list[j]);
                    //printf("VARCHAR : %s\n", (char *)t);
                    tcount = sprintf(tupleptr, "%s;", (char *)t);
                    break;

                    case DATE:
                    //printf("DATE : %s\n", (char *)t);
                    tcount = sprintf(tupleptr, "%s;", (char *)t);
                    break;
                }
                tupleptr = tupleptr + tcount;
            }

            temp = ptr_increment(t_ptr->size, temp);
            // Send the tuple
            send(senfd, tuple, MAX_VARCHAR * (col_count + 5), 10);
            //printf("%s\n", tuple);
        }

        //printf("Printing after block %d\n", block_count - 1);
    }
    free(tuple);
}

void
select_query(char *data, int senfd)
{
    int no_of_tabs, no_of_cols, no_of_wh, i, *sel_cols_t, *sel_cols_n,
            *wh_left_t, *wh_left_n, *wh_right_t, *wh_op;
    void **wh_right_n;
    char *line, *tables[2];

    sscanf(data, "%d", &no_of_tabs);

    //printf("tab = %d\n", no_of_tabs);

    for(i = 0; i < no_of_tabs; i++)
    {
        line = strchr(data, '\n');
        data = line + 1;
        tables[i] = (char*) malloc(sizeof(char) * (MAX_NAME + 1));
        sscanf(data, "%s", tables[i]);
        //printf("Tab %d = %s\n", i, tables[i]);
    }

    line = strchr(data, '\n');
    data = line + 1;
    sscanf(data, "%d", &no_of_cols);
    sel_cols_t = (int *) malloc(sizeof(int) * no_of_cols);
    sel_cols_n = (int *) malloc(sizeof(int) * no_of_cols);

    for(i = 0; i < no_of_cols; i++)
    {
        line = strchr(data, '\n');
        data = line + 1;
        sscanf(data, "%d %d", &sel_cols_t[i], &sel_cols_n[i]);
    }

    line = strchr(data, '\n');
    data = line + 1;
    sscanf(data, "%d", &no_of_wh);
    wh_left_t = (int *) malloc(sizeof(int) * no_of_wh);
    wh_left_n = (int *) malloc(sizeof(int) * no_of_wh);
    wh_right_t = (int *) malloc(sizeof(int) * no_of_wh);
    wh_right_n = (void**) malloc(sizeof(void*) * no_of_wh);
    wh_op = (int *) malloc(sizeof(int) * no_of_wh);

    for(i = 0; i < no_of_wh; i++)
    {
        line = strchr(data, '\n');
        data = line + 1;
        sscanf(data, "%d %d %d %d", &wh_left_t[i], &wh_left_n[i], &wh_op[i], &wh_right_t[i]);
        //printf("Input wh cl %d\n", i);
        fflush(stdout);
        if(wh_right_t[i] == -(INTEGER))
        {
            wh_right_n[i] = malloc(sizeof(int));
            sscanf(data, "%d %d %d %d %d", &wh_left_t[i], &wh_left_n[i], &wh_op[i], &wh_right_t[i], ((int*)wh_right_n[i]));
            //printf("Wh %d  = %d %d %d %d %d(const)\n", i, wh_left_t[i], wh_left_n[i], wh_op[i], wh_right_t[i], *((int*)wh_right_n[i]));
            fflush(stdout);
        }
        else if(wh_right_t[i] == -(VARCHAR))
        {
            wh_right_n[i] = malloc(sizeof(char) * (MAX_VARCHAR + 1));
            sscanf(data, "%d %d %d %d %s", &wh_left_t[i], &wh_left_n[i], &wh_op[i], &wh_right_t[i], ((char*)wh_right_n[i]));
            //printf("Wh %d  = %d %d %d %d %s\n", i, wh_left_t[i], wh_left_n[i], wh_op[i], wh_right_t[i], ((char*)wh_right_n[i]));
            fflush(stdout);
        }
        else if(wh_right_t[i] == -(DATE))
        {
            wh_right_n[i] = malloc(sizeof(char) * (DATE_LENGTH + 1));
            sscanf(data, "%d %d %d %d %s", &wh_left_t[i], &wh_left_n[i], &wh_op[i], &wh_right_t[i], ((char*)wh_right_n[i]));
            //printf("Wh %d  = %d %d %d %d %s\n", i, wh_left_t[i], wh_left_n[i], wh_op[i], wh_right_t[i], ((char*)wh_right_n[i]));
            fflush(stdout);
        }
        else
        {
            wh_right_n[i] = malloc(sizeof(int));
            sscanf(data, "%d %d %d %d %d", &wh_left_t[i], &wh_left_n[i], &wh_op[i], &wh_right_t[i], ((int*)wh_right_n[i]));
            //printf("Wh %d  = %d %d %d %d %d(col)\n", i, wh_left_t[i], wh_left_n[i], wh_op[i], wh_right_t[i], *((int*)wh_right_n[i]));
            fflush(stdout);
        }
    }
    //printf("All received no_of tables = %d!!\n", no_of_tabs);
    //fflush(stdout);

    if(no_of_tabs == 1)
    {
        //Single Table Query
        struct table *temp, *erase, *main_table;
        //Single Table Query
        std::vector<int> op[MAX_ATT];
        std::vector<void*> val[MAX_ATT];
        for(i = 0; i < no_of_wh; i++)
        {
            op[wh_left_n[i]].push_back(wh_op[i]);
            val[wh_left_n[i]].push_back(wh_right_n[i]);
        }
        //printf("wh done\n");
        temp = load_struct(tables[0]);
        main_table = temp;
        temp->blockbuf = malloc(temp->BLOCKSIZE);
        temp->blocknum = 0;

        temp->fp = open_file(tables[0], 2, const_cast<char*>("r+"), 0);
        //struct table *brute_find_record(struct table *t_ptr, int nth, int *opp[], void *rvalue[], int size)
        //printf("Exe\n");

        if(op[0].size() != 0)
        {
            erase = temp;
            temp = key_find_record(temp, op[0], val[0], op[0].size());
        }

        for (i = 1; i < MAX_ATT; i++)
        {
            erase = temp;
            if (op[i].size() != 0)
            {
                temp = brute_find_record(temp, i, op[i], val[i], op[i].size());
            }
        }

        column_select(temp, sel_cols_n, no_of_cols, senfd);
        //printf("Hell\n");
        //Choose your Function Here


        for(i = 0; i < MAX_NAME + 1; i++)
        {
            op[i].clear();
            val[i].clear();
        }
    }
    else if(no_of_tabs == 2)
    {
        //Join
        if(SERVER_OUTPUT)
            printf("JOIN OPERATION REQUESTED\n");
    }
    else if(SERVER_OUTPUT)
    {
        printf("More than 2 tables in JOIN !! Operation Not Yet Supported !!\n");
        fflush(stdout);
    }

    free(sel_cols_t);
    free(sel_cols_n);
    free(wh_left_t);
    free(wh_left_n);
    free(wh_right_t);
    free(wh_op);
    for(i = 0; i < no_of_wh; i++)
        free(wh_right_n[i]);
    free(wh_right_n);
}
