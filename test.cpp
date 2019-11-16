
#include <iostream>

using namespace std;

int main() {
    int a, b, c, d, e, n;
    n = 10;
    for (c = 1, a = 1; a < n; a++) {
        for (b = 1; b <= (n - a); b++) {
            cout << " ";
        }
        cout << "*";
        if (c > 1) {
            for (d = 1; d < (c * 2) - 2; d++) {
                cout << " ";
            }
            cout << "*";
        }
        c++;
        cout << endl;
    }
    for (e = 1; e <= n; e++) {
        cout << "* ";
    }
    cout << "\n";
}
