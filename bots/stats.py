import random
import time

class probabaility:

    def generate_probability(self):
        random.seed(time.time())
        return random.uniform(0, 1)

    def perform_action(self):
        p = self.generate_probability()
        if p >= 0.5:
            return True
        else:
            return False