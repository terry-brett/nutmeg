# a:0:{} user_score
import db_engine
import os
import random

db = db_engine.queries()

query = "SELECT SUM(user_infected) FROM user WHERE user_infected=1"
res = db.select(query, None)#

#for i in range(18, 41):
#    query = "INSERT INTO user (username, password, user_infected, user_susceptible, items_left) VALUES (%s, %s, %s, %s, %s)"
#    db.insert(query, ("test" + str(i), "$2y$10$eu4i.dGK1YOY5OPkm63bi.OL2FbiLnYLPN34NM2TnWy6i3z2ae2Z2", 0, 0, 2))

#for i in range(44, 80):
#    query = "INSERT INTO user_score (user_id, item_inventory) VALUES (%s, %s)"
#    db.insert(query, (i, "a:0:{}"))


def run_ba(m, fracion_of_infected):
    os.system("python .\../users/admin/sql_network_generator.py Barabasi-Albert " + str(m) + " " + str(fracion_of_infected))

def run_er(p, fracion_of_infected):
    os.system("python .\../users/admin/sql_network_generator.py ER " + str(p) + " " + str(fracion_of_infected))

def run_ws(m, p, fracion_of_infected):
    os.system("python .\../users/admin/sql_network_generator.py Watts-Strogatz " + str(m) + " " + str(p) + " " + str(fracion_of_infected)) # is_connected, if not generate again

def run_complete_graph(fracion_of_infected):
    os.system("python .\../users/admin/sql_network_generator.py Complete_graph " + str(fracion_of_infected))

def remove_items():
    folder = '/var/www/html/nutmeg/items'
    for the_file in os.listdir(folder):
        file_path = os.path.join(folder, the_file)
        try:
            if os.path.isfile(file_path):
                os.unlink(file_path)
            # elif os.path.isdir(file_path): shutil.rmtree(file_path)
        except Exception as e:
            print(e)

def reset_round():
    query = "UPDATE user_score SET items_total_value = 0, times_infected = 0, times_recovered =0, clean_message_sent = 0, clean_message_recieved = 0, infected_message_sent = 0, infected_message_recieved = 0"
    db.update(query, None)

    query = "UPDATE user_score SET item_inventory='a:0:{}', infected_by='', final_round_score=0"
    db.update(query, None)

    query = "UPDATE user SET user_infected = 0, user_susceptible=0"
    db.update(query, None)

    query = "DELETE FROM timeline"
    db.update(query, None)

    query = "DELETE FROM round_score"
    db.update(query, None)

def clear_users():
    for i in ids:
        query = "UPDATE `user` SET `user_infected`=False, `user_susceptible`=False WHERE `user_id`=" + str(i)
        db.update(query, None)

def reseed(ids):
    infected_list = random.sample(ids, round(0.1*len(ids)))
    clear_users()
    for i in infected_list:
        query = "UPDATE `user` SET `user_infected`=True WHERE `user_id`=" + str(i)
        db.update(query, None)

    query = "UPDATE user_score SET item_inventory='a:0:{}', infected_by='', final_round_score=0"
    db.update(query, None)

'''
query = "UPDATE user_score SET item_inventory='a:0:{}'"
db.update(query, None)

query = "UPDATE user_score SET items_total_value = 0, times_infected = 0, times_recovered =0, clean_message_sent = 0, clean_message_recieved = 0, infected_message_sent = 0, infected_message_recieved = 0, infected_by='', final_round_score=0"
db.update(query, None)

query = "UPDATE user SET user_infected = 0, user_susceptible=0"
db.update(query, None)

query = "DELETE FROM timeline"
db.update(query, None)

query = "DELETE FROM round_score"
db.update(query, None)
'''

query = "SELECT user_id FROM `user` WHERE `username` NOT IN ('admin')"
result = db.select(query, None)
ids = []

for i in result:
    ids.append(i[0])

#reset_round()
remove_items()
reseed(ids)

#query = "UPDATE user_score SET item_inventory='a:0:{}'"
#db.update(query, None)
#remove_items()
#run_ba(2, 0.8)
#run_complete_graph(0.8)
#run_ws(2, 0.2, 0.5)
