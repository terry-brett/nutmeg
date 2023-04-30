import mysql.connector as con
import networkx as nx
import sys
import random


def split(a, n):
    k, m = divmod(len(a), n)
    return (a[i * k + min(i, m):(i + 1) * k + min(i + 1, m)] for i in range(n))


def get_by_indecies(group, neighbours):
    a = []
    for i in range(len(neighbours)):
        a.append(group[neighbours[i]])
    return a


def map_nodes_to_id(nodes, ids):
    new_ids = []
    for n in nodes:
        new_ids.append(ids[n])
    return new_ids


def row_exists(user_id):
    cursor = mydb.cursor()
    cursor.execute("SELECT * FROM `friends` WHERE `user_id`=" + str(user_id))
    result = cursor.fetchall()
    if (len(result) > 0):
        return True
    else:
        return False


def connect_friends(node, friend_list):
    cursor = mydb.cursor()
    if (row_exists(node)):
        cursor.execute("UPDATE `friends` SET `list_of_friends`='" + str(friend_list)[1:-1].replace(" ",
                                                                                                   "") + "' WHERE `user_id`=" + str(
            node))
    else:
        cursor.execute(
            "INSERT INTO `friends` (user_id, list_of_friends) VALUES (" + str(node) + ", '" + str(friend_list)[
                                                                                              1:-1].replace(" ",
                                                                                                            "") + "')")
    mydb.commit()


def clear_users():
    cursor = mydb.cursor()
    for i in ids:
        cursor.execute("UPDATE `user` SET `user_infected`=False, `user_susceptible`=False WHERE `user_id`=" + str(i))
    mydb.commit()


def infect_nodes(infected_list):
    clear_users()
    cursor = mydb.cursor()
    for i in infected_list:
        cursor.execute("UPDATE `user` SET `user_infected`=True WHERE `user_id`=" + str(i))
    mydb.commit()


def create_groups(group, g, num_sel):
    nodes = []
    for i in range(0, len(group)):
        nodes.append(get_by_indecies(group, list(g.neighbors(i))))

    infected_list = random.sample(ids, num_sel)
    infect_nodes(infected_list)

    s = "{"
    for i in range(0, len(nodes)):
        mps = map_nodes_to_id(nodes[i], ids)
        connect_friends(ids[group[i]], mps)
        s += str(ids[group[i]]) + ": " + str(mps) + ", "

    s = s[:-2]
    s += "}"

    s_i = ""
    for i in infected_list:
        s_i += "G.node.get(" + str(i) + ").color = '#F00';"

    print(s + "_" + s_i)


mydb = con.connect(
    host="localhost",
    user="root",
    passwd="mysql",
    database="user_db"
)

cursor = mydb.cursor()

cursor.execute("SELECT user_id FROM `user` WHERE `username` NOT IN ('admin')")

result = cursor.fetchall()

ids = []

for i in result:
    ids.append(i[0])

net_gen = "Barabasi-Albert"
n = len(ids)
m = 2
p = 0.5
num_sel = round(0.1 * n)
n_groups = 1
a = range(0, n)

if (len(sys.argv) > 1):
    net_gen = str(sys.argv[1])

g = None


def gen_ws():
    G = nx.watts_strogatz_graph(n, m, p)
    if nx.is_connected(G):
        return G
    else:
        gen_ws()


if (net_gen == "Barabasi-Albert"):
    num_sel = round(float(sys.argv[3]) * n)
    m = int(sys.argv[2])
    groups = split(a, n_groups)
    for group in groups:
        g = nx.barabasi_albert_graph(len(group), m)
        create_groups(group, g, num_sel)
elif (net_gen == "Watts-Strogatz"):
    m = int(sys.argv[2])
    p = float(sys.argv[3])
    num_sel = round(float(sys.argv[4]) * n)
    groups = split(a, n_groups)
    for group in groups:
        while g == None:
            g = gen_ws()
        create_groups(group, g, num_sel)
elif (net_gen == "ER"):
    p = float(sys.argv[2])
    num_sel = round(float(sys.argv[3]) * n)
    groups = split(a, n_groups)
    for group in groups:
        g = nx.erdos_renyi_graph(len(group), p)
        create_groups(group, g, num_sel)
elif (net_gen == "Complete_graph"):
    num_sel = round(float(sys.argv[2]) * n)
    groups = split(a, n_groups)
    for group in groups:
        g = nx.complete_graph(len(group))
        create_groups(group, g, num_sel)
