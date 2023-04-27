import matplotlib.pyplot as plt
import glob, os
import numpy as np

temporal_files = []

prob_of_opening = np.arange(0.1, 0.9, 0.1)
for p in prob_of_opening:
   os.mkdir(str(round(p, 1)))

def get_avg(folder):
    sum = []
    for file in glob.glob(folder + "*.infected"):
        f = open(file)
        val = int(f.read())
        f.close()
        sum.append(val)
    return np.mean(sum)/40


def get_infected_array(p):
    folder = "cg/" + str(round(p, 1))
    infected_arr = []
    for file in glob.glob(folder):
        for p in prob_of_opening:
            avg = get_avg(file + "/" + str(round(p, 1)) + "/")
            infected_arr.append(avg )
    return infected_arr


a = []
for i in prob_of_opening:
    a.append(get_infected_array(i))

print (a)

x = prob_of_opening
y = prob_of_opening

X, Y = np.meshgrid(x, y)
#Z = np.sin(X)*np.cos(Y)
#Z = np.sqrt(X**2 + Y**2)
Z = a
cp = plt.contourf(X, Y, Z)
plt.title("Complete Graph")
plt.ylabel("Probability of opening the message")
plt.xlabel("Initial Seed")
plt.colorbar(cp)
plt.show()