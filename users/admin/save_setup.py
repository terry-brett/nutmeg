import sys

def get_constants():
    f = open("../constants.xml", "r")
    out = ""
    for line in f:
        out += line
    return out


round_number = sys.argv[1]
model = sys.argv[2]

outputstring = "<round_info>\n" \
               "\t<level>" + str(round_number) + "</level>\n" \
                "\t<setup>\n"\
                "\t\t<graph>" + model.upper() + "</graph>\n"\
                "\t\t<constants>\n" + get_constants() + "\n\t\t</constants>\n"\
                "\t</setup>"\
                "\n</round_info>"

f = open("../data/round_setup/" + str(round_number) +".xml", "w+")
f.write(outputstring)
f.close()