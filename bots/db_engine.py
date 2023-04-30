import mysql.connector as con

class queries:
    mydb = con.connect(
        host = "localhost",
        user = "root",
        passwd = "mysql",
        database = "user_db"
    )
    mydb.autocommit = True
    cursor = mydb.cursor()

    def close_con(self):
        if (self.mydb.is_connected()):
            self.cursor.close()
            self.mydb.close()

    def insert(self, query, insert_tuple):
        try:
            self.cursor.execute(query, insert_tuple)
            self.mydb.commit()
            print ("Insert Successful")
        except con.Error as error:
            print ("Query failed {}".format(error))

    def update(self, query, insert_tuple):
        try:
            self.cursor.execute(query, insert_tuple)
            self.mydb.commit()
            print ("Update Successful")
        except con.Error as error:
            print("Query failed {}".format(error))

    def select(self, query, insert_tuple):
        try:
            if insert_tuple == None: # tuple is empty
                self.cursor.execute(query)
                res = self.cursor.fetchall()
                return res
            else:
                self.cursor.execute(query, insert_tuple)
                res = self.cursor.fetchall()
                return res
        except con.Error as error:
            print("Query failed {}".format(error))

    def get_infected(self):
        query = "SELECT SUM(user_infected) FROM user WHERE user_infected=1"
        self.cursor.execute(query)
        res = self.cursor.fetchall()
        return res[0][0]
