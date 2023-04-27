import base64
import time
import json
import db_engine
import random
import items
import os

class behaviour:

    db = db_engine.queries()
    items = items.items_manager()

    def is_infected(self, bot_id):
        query = "SELECT user_infected FROM user WHERE user_id=%s"
        res = self.db.select(query, (bot_id,))
        return res

    def get_username(self, bot_id):
        query = "SELECT username FROM user WHERE user_id=%s"
        res = self.db.select(query, (bot_id,))
        return res[0][0]

    def is_blocked(self, from_id, to_id):
        query = "SELECT blocked_list FROM friends WHERE user_id=%s"
        res = self.db.select(query, (to_id,))[0][0]
        if res is not None:
            blocked_list = res.split(",")
            blocked_list = list(filter(None, blocked_list))
            blocked_list = list(map(int, blocked_list))
            if from_id in blocked_list:
                return True
            else:
                return False

    def get_round_num(self):
        f = open("../users/admin/round.txt")
        return f.read()

    def open_message(self, message_id):
        bot_id = message_id.split("_")[0]
        if self.items.message_infected(message_id):
            query = "UPDATE user SET user_infected=True,user_susceptible=False WHERE user_id=%s"
            new_name = base64.b64encode(str.encode(message_id))
            query = "UPDATE round_score SET infected_by_in_round=%s WHERE user_id=%s AND round_num=%s"
            infected_by = self.get_username(int(message_id.split("_")[1]))
            print(infected_by)
            infected_by = infected_by + ","
            self.db.update(query, (infected_by, bot_id, self.get_round_num()))
            try:
                os.rename("../items/" + message_id, "../items/" + str(new_name) + ".json")
            except Exception:
                pass
            self.db.update(query, (bot_id,))
            self.items.append_items(bot_id, new_name)
        else:
            try:
                new_name = base64.b64encode(str.encode(message_id))
                os.rename("../items/" + message_id, "../items/" + str(new_name) + ".json")
                self.items.append_items(bot_id, new_name)
            except Exception:
                pass


    def send_message(self, from_id, to_id):
        item_value = [1]
        random.seed(time.time())
        if not self.is_blocked(from_id, to_id):
            filename = "../items/" + str(to_id) + "_" + str(from_id) + "_" + str(int(time.time())) + ".json"
            loc = self.get_random_file(from_id)
            file_dict = {"origin":from_id,"to":[{"id":to_id,"opened":False}],"infected":self.is_infected(from_id)[0][0],"item_value":random.choice(item_value),"loc":loc}
            with open(filename, 'w+') as json_file:
                json.dump(file_dict, json_file)
            return True

    def get_random_file(self, bot_id):
        f_type = "safe"

        infected = self.is_infected(bot_id)[0][0]

        if (infected):
            f_type = "infected"

        directories = os.listdir("../users/content/" + f_type + "/")

        new_dir = random.choice(directories)
        if "links" in new_dir:
            return self.get_link(f_type)
        else:
            d = "../users/content/" + f_type + "/" + new_dir
            files = os.listdir(d + "/")
            file = random.choice(files)
            return d + "/" + file

    def get_link(self, f_type):
        dir = "../users/content/" + f_type + "/links/sites.txt"
        lines = open(dir).read().splitlines()
        myline = random.choice(lines)
        return myline

    def recover(self, bot_id):
        mu = 0.1
        infected = self.is_infected(bot_id)[0][0]
        random.seed(int(time.time()))
        if infected:
            if (random.uniform(0, 1) < mu):
                query = "UPDATE user SET user_infected=False WHERE user_id=%s"
                self.db.update(query, (bot_id,))
                print ("Recovery Successful")