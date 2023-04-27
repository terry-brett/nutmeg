import sched, time
import db_engine
import os

s = sched.scheduler(time.time, time.sleep)
db = db_engine.queries()



def get_infected():
    f = open("val")
    val = f.read()
    res = db.get_infected()
    if res == None:
        res = 0
    name = val + "/" + str(int(time.time())) + ".infected"
    f = open(name, "w+")
    f.write(str(res))
    print (res)
    f.close()
    if (res == 0 or res == 40):
        f = open("val", "w")
        value = float(val) + 0.1
        f.write(str(round(value, 1)))
        f.close()
        os.system(".\kill.bat")
    s.enter(1, 1, get_infected)


s.enter(0, 1, get_infected)
s.run()