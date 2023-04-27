import random
import os
import json
import phpserialize as php
import db_engine
import base64

class items_manager:

    db = db_engine.queries()

    def check_messages(self, bot_id):
        prefixed = [filename for filename in os.listdir('../items/') if filename.startswith(str(bot_id))]
        return prefixed

    def get_item_list(self, bot_id):
        query = "SELECT item_inventory FROM user_score WHERE user_id=%s"
        res = self.db.select(query, (bot_id,))[0][0]
        inventory = php.unserialize(str.encode(res))
        return inventory

    def message_infected(self, message_id):
        with open("../items/" + message_id) as json_file:
            data = json.load(json_file)
        if data["infected"]:
            return True
        else:
            return

    def add_score(self, from_id, to_id):
        query = "INSERT INTO timeline (user_id, recieved_from, sent_to_user, recieved_from_user, infected) VALUES(%s, %s, %s, %s, %s)"
        self.db.insert(query, (to_id, from_id, 0, 1, 0,))

    def increase_score(self, from_id, to_id):
        # get current score
        query = "SELECT sent_to_user, recieved_from_user FROM timeline WHERE user_id=%s AND recieved_from=%s"
        res = self.db.select(query, (to_id, from_id, ))
        if not res:
            self.add_score(from_id, to_id)
            self.add_score(to_id, from_id)
        else:
            sent_to = int(res[0][0])
            recieved_form = int(res[0][1])
            query = "UPDATE timeline SET recieved_from_user=%s WHERE user_id=%s AND recieved_from=%s"
            self.db.update(query, (str(recieved_form+1), to_id, from_id))

    def get_round_num(self):
        f = open("../users/admin/round.txt")
        return f.read()

    def increase_sent_round_score(self, from_id, bot_status):
        round_num = self.get_round_num()
        if bot_status[0][0] == 1:
            query = "SELECT * FROM round_score WHERE user_id=%s  AND round_num=%s"
            res = self.db.select(query, (from_id, round_num,))
            if not res:
                query = "INSERT INTO round_score (user_id, infected_messges_sent_in_round, round_num) VALUES(%s, 1, %s)"
                self.db.insert(query, (from_id, round_num,))
            else:
                infected_messges_sent_in_round = int(res[0][7])
                query = "UPDATE round_score SET infected_messges_sent_in_round=%s WHERE user_id=%s AND round_num=%s"
                self.db.update(query, (infected_messges_sent_in_round + 1, from_id, round_num))
        elif bot_status[0][0] == 0:
            query = "SELECT * FROM round_score WHERE user_id=%s  AND round_num=%s"
            res = self.db.select(query, (from_id, round_num,))
            if not res:
                query = "INSERT INTO round_score (user_id, clean_messges_sent_in_round, round_num) VALUES(%s, 1, %s)"
                self.db.insert(query, (from_id, round_num,))
            else:
                clean_messges_sent_in_round = int(res[0][5]) # this is none
                query = "UPDATE round_score SET clean_messges_sent_in_round=%s WHERE user_id=%s AND round_num=%s"
                self.db.update(query, (clean_messges_sent_in_round + 1, from_id, round_num))

    def increase_recieved_round_score(self, to_id, bot_status):
        round_num = self.get_round_num()

        if bot_status[0][0] == 1:
            query = "SELECT * FROM round_score WHERE user_id=%s  AND round_num=%s"
            res = self.db.select(query, (to_id, round_num,))
            if not res:
                query = "INSERT INTO round_score (user_id, infected_messges_received_in_round, round_num, infected_by_in_round) VALUES(%s, 1, %s)"
                self.db.insert(query, (to_id, round_num,))
            else:
                infected_messges_received_in_round = int(res[0][8])
                query = "UPDATE round_score SET infected_messges_received_in_round=%s WHERE user_id=%s AND round_num=%s"
                self.db.update(query, (infected_messges_received_in_round + 1, to_id, round_num,))
        elif bot_status[0][0] == 0:
            query = "SELECT * FROM round_score WHERE user_id=%s  AND round_num=%s"
            res = self.db.select(query, (to_id, round_num,))
            if not res:
                query = "INSERT INTO round_score (user_id, clean_messges_received_in_round, round_num) VALUES(%s, 1, %s)"
                self.db.insert(query, (to_id, round_num,))
            else:
                clean_messges_received_in_round = int(res[0][6])
                query = "UPDATE round_score SET clean_messges_received_in_round=%s WHERE user_id=%s AND round_num=%s"
                self.db.update(query, (clean_messges_received_in_round + 1, to_id, round_num))


    def update_running_rank(self, bot_id):
        items_value = [1]
        value = random.choice(items_value)
        total = 0

        round_num = self.get_round_num()

        query = "SELECT * FROM `round_score` WHERE `user_id`=%s AND round_num=%s"
        res = self.db.select(query, (bot_id, round_num,))

        if res:
            total = res[0][2]

        total = total + value

        query = "SELECT * FROM `round_score` WHERE `user_id`=%s AND round_num=%s"
        res = self.db.select(query, (bot_id, round_num,))
        if res:
            query = "UPDATE `round_score` SET `total`=%s WHERE `user_id`=%s AND round_num=%s"
            self.db.update(query, (total, bot_id, round_num,))
        else:
            query = "INSERT INTO `round_score` (`user_id`, `total`, `round_num`) VALUES (%s, %s, %s)"
            self.db.insert(query, (bot_id, total, round_num,))

        query = "UPDATE `user_score` SET `items_total_value`=%s WHERE `user_id`=%s"
        self.db.update(query, (total, bot_id,))

    def append_items(self, bot_id, item_id):
        query = "SELECT item_inventory FROM user_score WHERE user_id=%s"
        res = self.db.select(query, (bot_id,))[0][0]
        inventory = php.unserialize(str.encode(res))
        inventory.update({len(inventory) : item_id})
        inventory = php.serialize(inventory)
        query = "UPDATE user_score SET item_inventory=%s WHERE user_id=%s"
        self.db.update(query, (inventory, bot_id,))
        self.update_total(bot_id)

    def remove_item(self, bot_id, random_key):
        query = "SELECT item_inventory FROM user_score WHERE user_id=%s"
        res = self.db.select(query, (bot_id,))[0][0]
        inventory = php.unserialize(str.encode(res))
        del inventory[random_key]
        inventory = php.serialize(inventory)
        query = "UPDATE user_score SET item_inventory=%s WHERE user_id=%s"
        self.db.update(query, (inventory, bot_id,))
        self.update_total(bot_id)

    def update_total(self, bot_id):
        items = [filename for filename in os.listdir('../items/') if filename.startswith(str(bot_id))]
        total = 0
        for i in items:
            with open("../items/" + i) as json_file:
                data = json.load(json_file)
                total += data["item_value"]

        query = "UPDATE user_score SET items_total_value=%s WHERE user_id=%s"
        self.db.update(query, (total, bot_id,))

    def get_total(self, bot_id):
        query = "SELECT items_total_value FROM user_score WHERE user_id=%s"
        score = self.db.select(query, (bot_id,))[0][0]
        if score == None:
            return 0
        else:
            return score

    def get_target(self):
        f = open("../users/target", "r")
        return f.read()

    def generate_target(self):
        f = open("../users/target", "w+")
        f.write(str(random.randint(25,150)))
        f.close()