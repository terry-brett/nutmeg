import random
import db_engine
import stats
import bot
import items
import sched, time
import sys

s = sched.scheduler(time.time, time.sleep)

db = db_engine.queries()
prob = stats.probabaility()
bot = bot.behaviour()
items = items.items_manager()

def get_bot_id(username):
    query = "SELECT user_id FROM user WHERE username=%s"
    res = db.select(query, (username, ))
    return res

def get_friends_list(bot_id):
    query = "SELECT list_of_friends FROM friends WHERE user_id=%s"
    res = db.select(query, (bot_id,))
    return res

def open_messages():
    item_list = items.check_messages(bot_id)
    f = open("val")
    val = f.read()
    open_prob = float(val)
    #print (len(item_list))
    for i in item_list:
        if open_prob > random.uniform(0, 1): # set the probability to 1 to check if everyone is infected
            print(items.message_infected(i))
            print ("Message opened")
            bot.open_message(i)
    t = random.randint(1, 10)
    s.enter(t, 1, open_messages)


def send():
    random.seed(time.time())
    random_friend_id = random.choice(list_of_friends)
    if prob.perform_action():
        if not bot.is_blocked(bot_id, random_friend_id):
            if bot.send_message(bot_id, random_friend_id):
                items.update_running_rank(bot_id)
                items.increase_score(bot_id, random_friend_id)
                items.increase_score(random_friend_id, bot_id)
                items.increase_sent_round_score(bot_id, bot.is_infected(bot_id))
                items.increase_recieved_round_score(random_friend_id, bot.is_infected(bot_id))
                print("Message sent successfully")
    t = random.randint(1, 10)
    s.enter(t, 1, send)

def remove_item():
    item_list = items.get_item_list(bot_id)
    if len(item_list) > 0:
        if prob.perform_action():
            random_key, random_val = random.choice(list(item_list.items()))
            items.remove_item(bot_id, random_key)
            print ("Message removed")
    t = random.randint(1, 10)
    s.enter(t, 1, remove_item)

def recover():
    bot.recover(bot_id)
    t = random.randint(1, 10)
    s.enter(1, 1, recover)

def random_friend_generator():
    global random_friend_id
    random_friend_id = random.choice(list_of_friends)

#items.generate_target()

username = sys.argv[1]
#username = "user3"

bot_id = get_bot_id(username)[0][0]
list_of_friends = get_friends_list(bot_id)[0][0].split(",")

s.enter(0.1, 1, open_messages)
s.enter(0.1, 1, send)
s.enter(0.1, 1, remove_item)
#s.enter(1, 1, recover)
s.run()


