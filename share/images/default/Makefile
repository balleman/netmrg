all :
	@echo "Making Images"
	batik-rasterizer -h 12 arrow.svg
	batik-rasterizer -h 15 disk.svg
	batik-rasterizer -h 15 viewgraph.svg
	batik-rasterizer -h 35 -d netmrg-logo-small.png netmrg-logo.svg
	batik-rasterizer -h 100 -d netmrg-logo-medium.png netmrg-logo.svg
	batik-rasterizer -h 250 -d netmrg-logo-large.png netmrg-logo.svg
	cat status.svg | perl -ne 's/#7f7f7f/#ff0000/; s/#4c4c4c/#ff4c4c/; print;' > status-critical-trig.svg
	batik-rasterizer -h 15 status-critical-trig.svg
	cat status.svg | perl -ne 's/#7f7f7f/#ff0000/; s/#4c4c4c/#7f4c4c/; print;' > status-critical-untrig.svg
	batik-rasterizer -h 15 status-critical-untrig.svg
	cat status.svg | perl -ne 's/#7f7f7f/#00bf00/; s/#4c4c4c/#34cb32/; print;' > status-good-trig.svg
	batik-rasterizer -h 15 status-good-trig.svg
	cat status.svg | perl -ne 's/#7f7f7f/#00bf00/; s/#4c4c4c/#347f34/; print;' > status-good-untrig.svg
	batik-rasterizer -h 15 status-good-untrig.svg
	cat status.svg | perl -ne 's/#7f7f7f/#0000bf/; s/#4c4c4c/#4c4cc0/; print;' > status-unknown-trig.svg
	batik-rasterizer -h 15 status-unknown-trig.svg
	cat status.svg | perl -ne 's/#7f7f7f/#0000bf/; s/#4c4c4c/#4c4c7f/; print;' > status-unknown-untrig.svg
	batik-rasterizer -h 15 status-unknown-untrig.svg
	cat status.svg | perl -ne 's/#7f7f7f/#e6e600/; s/#4c4c4c/#e6e500/; print;' > status-warning-trig.svg
	batik-rasterizer -h 15 status-warning-trig.svg
	cat status.svg | perl -ne 's/#7f7f7f/#e6e600/; s/#4c4c4c/#999900/; print;' > status-warning-untrig.svg
	batik-rasterizer -h 15 status-warning-untrig.svg

clean:
	rm -f status-unknown-trig.svg status-unknown-untrig.svg status-good-trig.svg status-good-untrig.svg status-warning-trig.svg status-warning-untrig.svg status-critical-trig.svg status-critical-untrig.svg

